<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Directory\People;
use App\Manager\Directory\PeopleManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tmp:user:usernames')]
class ShopfloorUserUsernameCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Temporary command to update usernames of shopfloor users');
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('p')
            ->from(People::class, 'p')
            ->join('p.acls', 'a')
            ->join('a.group', 'g')
            ->andWhere('g.name = :piGroup')
            ->andWhere('p.disabled = 0')
            ->andWhere('p.hidden = 0')
            ->setParameters(new ArrayCollection([
                new Parameter('piGroup', 'PI_OPERATOR'),
            ]))
        ;

        /** @var People[] $users */
        $users = $qb->getQuery()->getResult();

        $usernames = [];

        $i = 0;
        foreach ($users as $user) {
            if (PeopleManager::hasGroup($user, 'ACL_AUTH_INTRANET')) {
                continue;
            }

            [$username] = explode('@', $user->getUserIdentifier());

            if (\in_array($username, $usernames, true)) {
                $username .= '.'.($user->getErpIdentifier() ?? $user->getId());
            }

            $usernames[] = $username;
            $message = \sprintf('User %s username was updated to %s', $user->getUserIdentifier(), $username);
            $user->setUsername($username);
            $this->entityManager->persist($user);
            $output->writeln($message);

            ++$i;
            if (0 === $i % 100) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        return 0;
    }
}

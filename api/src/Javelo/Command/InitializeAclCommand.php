<?php

declare(strict_types=1);

namespace App\Javelo\Command;

use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Javelo\Repository\UserRepository;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:human-resources:javelo:initialize:acl', description: 'Initialize Javelo Acl')]
class InitializeAclCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PeopleRepository $peopleRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $javeloGroup = $this->entityManager->getRepository(Group::class)->findOneBy(['name' => 'ACL_AUTH_JAVELO']);
        if (!$javeloGroup) {
            $javeloGroup = new Group();
            $javeloGroup->setName('ACL_AUTH_JAVELO');
            $javeloGroup->setDescription('Access authentification control for accessing Javelo Application');
            $this->entityManager->persist($javeloGroup);
        }

        $updated = 0;

        $synchronizedPeopleIds = $this->userRepository->searchPeopleConcernedBySynchronization();
        $peopleToUpdate = $this->peopleRepository->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', array_column($synchronizedPeopleIds, 'id'))
            ->getQuery()
        ->getResult();
        $progressBar = new ProgressBar($output, \count($peopleToUpdate));
        /** @var People $people */
        foreach ($peopleToUpdate as $people) {
            $existingAcl = $people->getAcls()->filter(
                static fn (Acl $acl) => $acl->getGroup() === $javeloGroup
            );

            if (0 === \count($existingAcl)) {
                $acl = new Acl();
                $acl->setUser($people);
                $acl->setGroup($javeloGroup);
                $people->addAcl($acl);
                $this->entityManager->persist($acl);
                ++$updated;
            }
        }

        $this->entityManager->flush();

        $progressBar->finish();
        $output->writeln('...');
        $output->writeln(\sprintf('%d people updated on javelo.', $updated));

        return Command::SUCCESS;
    }
}

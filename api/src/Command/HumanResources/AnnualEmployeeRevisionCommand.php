<?php

declare(strict_types=1);

namespace App\Command\HumanResources;

use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Notifier\Tasks\SequenceNotifier;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Factory\Sequence\HumanResources\AnnualRevisionSequenceFactory;
use LegacyBundle\Manager\SequenceManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:human-resources:annual-revision')]
class AnnualEmployeeRevisionCommand extends Command
{
    public function __construct(
        private readonly SequenceManager $sequenceManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly SequenceNotifier $notifier,
        private readonly AnnualRevisionSequenceFactory $factory,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleRepository = $this->entityManager->getRepository(People::class);

        foreach ($peopleRepository->findBy(['disabled' => false, 'hidden' => false]) as $people) {
            if (null === $people->getCreatedAt()) {
                continue;
            }

            if ($people->getCreatedAt() >= new \DateTime('first day of this year')) {
                continue;
            }

            $assignee = $people;
            $template = 'user.annual_revision';
            if ($people->getAcls()->filter(static fn (Acl $acl) => 'ACL_AUTH_INTRANET' === $acl->getGroup()->getName())->isEmpty()) {
                $assignee = $people->getSupervisor();
                $template = 'user.annual_revision.light';
            }

            $sequence = $this->factory->createAnnualRevisionSequence($people, $template, $assignee);

            $this->sequenceManager->insert($sequence);
            $this->notifier->sendEmail($sequence);
        }

        return Command::SUCCESS;
    }
}

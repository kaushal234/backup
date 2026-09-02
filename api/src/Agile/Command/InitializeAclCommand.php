<?php

declare(strict_types=1);

namespace App\Agile\Command;

use App\Agile\SynchronizationFilters;
use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:human-resources:agile:initialize:acl', description: 'Initialize Agile Acl')]
class InitializeAclCommand extends Command
{
    public function __construct(
        private readonly PeopleRepository $peopleRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $agileGroup = $this->entityManager->getRepository(Group::class)->findOneBy(['name' => 'ACL_AUTH_AGILE']);

        if (!$agileGroup) {
            $agileGroup = new Group();
            $agileGroup->setName('ACL_AUTH_AGILE');
            $agileGroup->setDescription('Access authentification control for accessing Agile Application');
            $this->entityManager->persist($agileGroup);
        }

        $updated = 0;

        $synchronizedPeople = $this->peopleRepository->searchPeopleForAgileSynchronization(
            SynchronizationFilters::EXCLUDE_DIVISIONS_ID,
            SynchronizationFilters::EXCLUDE_BUSINESS_UNITS_ID,
            SynchronizationFilters::EXCLUDE_POSITIONS_ID,
            SynchronizationFilters::EXCLUDE_PEOPLE_ID);

        $progressBar = new ProgressBar($output, \count($synchronizedPeople));
        /** @var People $people */
        foreach ($synchronizedPeople as $people) {
            if ($people->isDisabled()) {
                continue;
            }

            $existingAcl = $people->getAcls()->filter(
                static fn (Acl $acl) => $acl->getGroup() === $agileGroup
            );

            if (0 < \count($existingAcl)) {
                continue;
            }

            $acl = new Acl();
            $acl->setUser($people);
            $acl->setGroup($agileGroup);
            $people->addAcl($acl);
            $this->entityManager->persist($acl);
            ++$updated;
        }

        $this->entityManager->flush();

        $progressBar->finish();
        $output->writeln('...');
        $output->writeln(\sprintf('%d get Agile Acl.', $updated));

        return Command::SUCCESS;
    }
}

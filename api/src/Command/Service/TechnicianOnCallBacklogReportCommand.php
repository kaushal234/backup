<?php

declare(strict_types=1);

namespace App\Command\Service;

use App\Entity\Directory\Location;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCall\BacklogReport;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:technician_on_call:backlog_report')]
class TechnicianOnCallBacklogReportCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $technicianOnCallRepository = $this->entityManager->getRepository(TechnicianOnCall::class);
        $locationRepository = $this->entityManager->getRepository(Location::class);
        $locations = $locationRepository->findBy([
            'capability.sso' => true,
        ]);

        foreach ($locations as $location) {
            $technicianOnCalls = $technicianOnCallRepository->findBy([
                'status' => TechnicianOnCall::OPENED_STATUSES,
                'salesOrganisationService' => $location,
            ]);

            $backlog = new BacklogReport();
            $backlog->salesOrganisation = $location;
            $backlog->technicianOnCalls = new ArrayCollection($technicianOnCalls);
            $backlog->date = (new \DateTimeImmutable('last day of previous month'))->setTime(23, 59, 59);

            $this->entityManager->persist($backlog);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

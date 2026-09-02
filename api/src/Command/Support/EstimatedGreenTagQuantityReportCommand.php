<?php

declare(strict_types=1);

namespace App\Command\Support;

use App\Entity\Support\EquipmentRecord\EstimatedGreenTagQuantityReport;
use App\Repository\Directory\LocationRepository;
use App\Repository\EquipmentRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:equipment:green_tag_quantity_report')]
class EstimatedGreenTagQuantityReportCommand extends Command
{
    public function __construct(
        private readonly EquipmentRecordRepository $equipmentRecordRepository,
        private readonly LocationRepository $locationRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'month',
                'm',
                InputOption::VALUE_REQUIRED,
                'Specify the month to generate green tag reports.',
                'first day of this month'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $month = new \DateTime($input->getOption('month'));
        } catch (\Exception $e) {
            $output->writeln(\sprintf('<error>%s</error>', $e->getMessage()));

            return Command::FAILURE;
        }

        $records = $this->equipmentRecordRepository->getEquipmentRecordsEstimatedGreenTagByMonth($month);

        $groupedRecords = [];
        foreach ($records as $record) {
            $location = $record->getManufacturerLocation();
            if (null === $location) {
                continue;
            }

            $dayInMonth = $record->getEstimatedGreenTagDate()->format('Y-m-d');
            $locationId = $location->getId();

            $groupedRecords[$dayInMonth][$locationId][] = $record;
        }

        foreach ($groupedRecords as $dayOfMonth => $locations) {
            $day = new \DateTime($dayOfMonth);
            $day->setTime(0, 0, 0);

            foreach ($locations as $locationId => $equipmentRecords) {
                $location = $this->locationRepository->find($locationId);
                if (null === $location) {
                    continue;
                }

                $existingReport = $this->entityManager
                    ->getRepository(EstimatedGreenTagQuantityReport::class)
                    ->findOneBy([
                        'day' => $day,
                        'manufacturerLocation' => $location,
                    ]);

                if (null !== $existingReport) {
                    continue;
                }

                $report = new EstimatedGreenTagQuantityReport();
                $report->day = $day;
                $report->manufacturerLocation = $location;

                foreach ($equipmentRecords as $equipmentRecord) {
                    $report->addEquipmentRecord($equipmentRecord);
                }

                $this->entityManager->persist($report);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

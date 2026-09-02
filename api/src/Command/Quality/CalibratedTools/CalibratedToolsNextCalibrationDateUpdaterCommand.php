<?php

declare(strict_types=1);

namespace App\Command\Quality\CalibratedTools;

use App\Entity\Quality\CalibratedTools\CalibrationLog;
use App\Entity\Quality\CalibratedTools\Tool;
use App\Repository\Quality\ToolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:calibrated_tools:update_next_calibration')]
class CalibratedToolsNextCalibrationDateUpdaterCommand extends Command
{
    private readonly ToolRepository $toolRepository;

    private readonly EntityManagerInterface $entityManager;

    /**
     * CalibratedToolsNextCalibrationDateUpdaterCommand constructor.
     */
    public function __construct(ToolRepository $toolRepository, EntityManagerInterface $registry)
    {
        parent::__construct();
        $this->setDescription('Update Calibrated Tools Next Calibration Date');
        $this->toolRepository = $toolRepository;
        $this->entityManager = $registry;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Tool[] $tools */
        $tools = $this->toolRepository->findBy([
            'status' => [Tool::ACTIVE, Tool::CALIBRATION_DUE_SOON, Tool::EXPIRED],
        ]);

        $pg = new ProgressBar($output, \count($tools));

        foreach ($tools as $tool) {
            $pg->advance();

            if (null !== $tool->getNextCalibrationDate() || $tool->getCalibrationLogs()->isEmpty()) {
                continue;
            }

            /** @var CalibrationLog $lastLog */
            $lastLog = $tool->getCalibrationLogs()->last();
            /** @var \DateTime $endDate */
            $endDate = clone $lastLog->getCalibrationDate();

            $nextCalibrationDate = $endDate->modify(\sprintf('+%d days', $tool->getCalibrationInterval()));

            $tool->setNextCalibrationDate($nextCalibrationDate);

            $this->entityManager->persist($tool);
        }

        $this->entityManager->flush();

        $pg->finish();

        return 0;
    }
}

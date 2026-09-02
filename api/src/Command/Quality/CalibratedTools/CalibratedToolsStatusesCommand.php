<?php

declare(strict_types=1);

namespace App\Command\Quality\CalibratedTools;

use App\Entity\Quality\CalibratedTools\CalibrationLog;
use App\Entity\Quality\CalibratedTools\Tool;
use App\Notifier\Quality\CalibratedToolNotifier;
use App\Repository\Quality\ToolRepository;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:notifications:calibrated_tools')]
class CalibratedToolsStatusesCommand extends Command
{
    private readonly WorkflowStatusUpdater $workflowStatusUpdater;
    private readonly EntityManagerInterface $em;
    private readonly CalibratedToolNotifier $notifier;

    public function __construct(WorkflowStatusUpdater $workflowStatusUpdater, EntityManagerInterface $entityManager, CalibratedToolNotifier $notifier)
    {
        parent::__construct();
        $this->setDescription('Update Calibrated Tools Statuses');
        $this->workflowStatusUpdater = $workflowStatusUpdater;
        $this->em = $entityManager;
        $this->notifier = $notifier;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTime();

        $output->writeln('<info>Starting Tools inspection</info>');

        /** @var ToolRepository $toolRepository */
        $toolRepository = $this->em->getRepository(Tool::class);
        $tools = $toolRepository->getActiveAndCalibrationDueSoonTools();

        $output->writeln(\sprintf('<info>Found %d Tools</info>', \count($tools)));

        $toolsNotification = [];
        foreach ($tools as $tool) {
            // If there are no calibration logs, skip the tool
            if ($tool->getCalibrationLogs()->isEmpty()) {
                // if the tool has no logs and its status is not out of service, something went wrong: let's reinitialize it
                if (!\in_array($tool->getStatus(), [Tool::OUT_OF_SERVICE, Tool::SCRAPPED], true)) {
                    $this->workflowStatusUpdater->applyStatus($tool, Tool::OUT_OF_SERVICE);
                    $this->em->persist($tool);
                }
                continue;
            }

            /** @var CalibrationLog $lastLog */
            $lastLog = $tool->getCalibrationLogs()->last();
            $statusChangeDate = null;
            $status = null;

            switch ($tool->getStatus()) {
                case Tool::ACTIVE:
                    if (null !== $tool->getNextCalibrationDate()) {
                        $nextCalibrationDate = clone $tool->getNextCalibrationDate();
                        $statusChangeDate = $nextCalibrationDate->modify(\sprintf('-%s days', $tool->getCalibrationNotice()));
                    } else {
                        /** @var \DateTime $endDate */
                        $endDate = clone $lastLog->getCalibrationDate();
                        $statusChangeDate = $endDate->modify(\sprintf('+%d days', $tool->getCalibrationInterval() - $tool->getCalibrationNotice()));
                    }
                    $status = Tool::CALIBRATION_DUE_SOON;
                    break;
                case Tool::CALIBRATION_DUE_SOON:
                    if (null !== $tool->getNextCalibrationDate()) {
                        $statusChangeDate = clone $tool->getNextCalibrationDate();
                    } else {
                        /** @var \DateTime $endDate */
                        $endDate = clone $lastLog->getCalibrationDate();
                        $statusChangeDate = $endDate->modify(\sprintf('+%d days', $tool->getCalibrationInterval()));
                    }
                    $status = Tool::EXPIRED;
                    break;
                default:
                    continue 2;
            }

            if (null !== $statusChangeDate && $now >= $statusChangeDate) {
                try {
                    $this->workflowStatusUpdater->applyStatus($tool, $status);
                    $this->em->persist($tool);

                    $toolsNotification[$tool->getLocationArea()->getSupervisor()->getEmail()][] = $tool;
                    $output->writeln(\sprintf('<info>Tool #%d updated to status %s</info>', $tool->getId(), $status));
                } catch (\Exception $exception) {
                    $output->writeln(\sprintf('<error>Something went wrong applying status %s to tool %d...</error>', $status, $tool->getId()));
                }
            }
        }
        $this->em->flush();

        $output->writeln(\sprintf('<info>Sending %d notifications...</info>', \count($toolsNotification)));

        foreach ($toolsNotification as $to => $tools) {
            $this->notifier->sendEmail($tools, $to);
        }

        $output->writeln(\sprintf('<info>Finished (Modified %d tools)</info>', \count($tools)));

        return 0;
    }
}

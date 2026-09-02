<?php

declare(strict_types=1);

namespace App\Command\MIS;

use App\Entity\Module\ThirdPartyApp\Member;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Factory\Common\Notification\Module\ThirdPartyAppUpdateTasksNotificationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:mis:third_party_app:update_task_alert', description: 'Send notifications to third party app admins if update tasks are opens.')]
class ThirdPartyAppUpdateTaskAlertCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ThirdPartyAppUpdateTasksNotificationFactory $factoryNotification,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $thirdPartyApps = $this->entityManager->getRepository(Extended::class)->findByUpdateTasksOpened();
        $memberRepository = $this->entityManager->getRepository(Member::class);

        $countNotifications = 0;
        foreach ($thirdPartyApps as $thirdPartyApp) {
            // Save all receivers in an array
            $receiversList = [];
            if (null !== $thirdPartyApp->getOperationalOwner()) {
                $receiversList[] = $thirdPartyApp->getOperationalOwner();
            }
            if (null !== $thirdPartyApp->getMainAdmin()) {
                $receiversList[] = $thirdPartyApp->getMainAdmin();
            }
            foreach ($memberRepository->findAdminsOfThirdPartyApp($thirdPartyApp) as $admin) {
                $receiversList[] = $admin->getUser();
            }

            // Send notifications after removing duplicate receiver
            foreach (array_unique($receiversList) as $receiver) {
                $this->entityManager->persist($this->factoryNotification->createNotification($thirdPartyApp, $receiver));
                ++$countNotifications;
            }
        }

        $this->entityManager->flush();

        if ($countNotifications) {
            $this->logger->info(\sprintf('[3PA] %s notifications for update tasks alert have been sent', $countNotifications));
        }

        return Command::SUCCESS;
    }
}

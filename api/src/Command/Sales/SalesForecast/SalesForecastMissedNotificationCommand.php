<?php

declare(strict_types=1);

namespace App\Command\Sales\SalesForecast;

use App\Doctrine\Voter\ActivityLogVoter;
use App\Entity\Sales\SalesForecast;
use App\Notifier\Sales\SalesForecast\SalesForecastNotifier;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:sales_forecast:notifications')]
class SalesForecastMissedNotificationCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;
    private readonly SalesForecastNotifier $notifier;
    private readonly ActivityLogVoter $activityLogVoter;

    public function __construct(
        EntityManagerInterface $entityManager,
        ActivityLogVoter $activityLogVoter,
        SalesForecastNotifier $notifier
    ) {
        parent::__construct();
        $this->setDescription('Send SFR notifications that were not sent because of wrong user actions');
        $this->entityManager = $entityManager;
        $this->activityLogVoter = $activityLogVoter;
        $this->notifier = $notifier;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->activityLogVoter->disable();

        /** @var SalesForecastRepository $repository */
        $repository = $this->entityManager->getRepository(SalesForecast::class);

        $notNotifiedSFRs = $repository->findClosedAndNotNotified(new \DateTime('30 minutes ago'), new \DateTime('60 minutes ago'));

        foreach ($notNotifiedSFRs as $notNotifiedSFR) {
            $notNotifiedSFR->setClosureNotificationSentAt(new \DateTime());

            $this->notifier->sendEmail($notNotifiedSFR, 'sfr.subject.closure', 'sales_forecast_closure.html.twig');
            $this->entityManager->persist($notNotifiedSFR);
        }

        $this->entityManager->flush();

        return 0;
    }
}

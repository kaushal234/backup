<?php

declare(strict_types=1);

namespace App\Command\Sales\SalesForecast;

use App\Doctrine\Voter\ActivityLogVoter;
use App\Entity\Sales\SalesForecast;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Timestampable\TimestampableListener;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:sales_forecast:update_date', description: 'Updates dates to put end of month')]
class SalesForecastUpdateEstimatedSalesDateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ActivityLogVoter $activityLogVoter,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->activityLogVoter->disable();

        $eventManager = $this->entityManager->getEventManager();
        /** @var array $eventManagerListeners */
        $eventManagerListeners = $eventManager->getAllListeners();
        foreach ($eventManagerListeners as $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof TimestampableListener) {
                    $eventManager->removeEventListener($listener->getSubscribedEvents(), $listener);
                }
            }
        }

        /** @var SalesForecastRepository $repository */
        $repository = $this->entityManager->getRepository(SalesForecast::class);

        foreach ($repository->findBy(['status' => SalesForecast::OPEN_STATUSES]) as $salesForecast) {
            $salesForecast->setEstimatedSaleDate(new \DateTime($salesForecast->getEstimatedSaleDate()->format('Y-m')));
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

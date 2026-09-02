<?php

declare(strict_types=1);

namespace App\Command\Sales\SalesForecast;

use App\Entity\Sales\SalesForecast;
use App\Factory\SalesForecastSnapshotFactory;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:sales_forecast:snapshot')]
class SalesForecastSnapshotCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;
    private readonly SalesForecastSnapshotFactory $salesForecastSnapshotFactory;

    public function __construct(EntityManagerInterface $entityManager, SalesForecastSnapshotFactory $salesForecastSnapshotFactory)
    {
        parent::__construct();
        $this->setDescription('Take a snapshot of Sales Forecasts');
        $this->entityManager = $entityManager;
        $this->salesForecastSnapshotFactory = $salesForecastSnapshotFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var SalesForecastRepository $salesForecastRepository */
        $salesForecastRepository = $this->entityManager->getRepository(SalesForecast::class);
        $openSalesForecasts = $salesForecastRepository->findOpen();

        $i = 0;
        foreach ($openSalesForecasts as $salesForecast) {
            $snapshot = $this->salesForecastSnapshotFactory->createSnapshot($salesForecast);

            $this->entityManager->persist($snapshot);

            ++$i;
            if (0 === $i % 100) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        return 0;
    }
}

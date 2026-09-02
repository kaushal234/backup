<?php

declare(strict_types=1);

namespace App\Command\Sales\SalesForecast;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\SalesForecast;
use App\Notifier\Sales\SalesForecast\SalesForecastNotifier;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:sales_forecast:delinquent-notifications')]
class SalesForecastDelinquentNotificationCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;
    private readonly SalesForecastNotifier $notifier;
    private readonly IriConverterInterface $iriConverter;

    public function __construct(
        EntityManagerInterface $entityManager,
        SalesForecastNotifier $notifier,
        IriConverterInterface $iriConverter
    ) {
        parent::__construct();
        $this->setDescription('Send an email with a delinquent Sales Forecasts summary');
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
        $this->iriConverter = $iriConverter;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->entityManager->getFilters()->disable('softdeleteable');
        /** @var SalesForecastRepository $salesForecastRepository */
        $salesForecastRepository = $this->entityManager->getRepository(SalesForecast::class);

        $salesForecastClassed = [];
        foreach ($salesForecastRepository->findOpenAndDeliquent() as $salesForecast) {
            $key = \sprintf('%s-%s', $this->iriConverter->getIriFromResource($salesForecast->getSso()), $this->iriConverter->getIriFromResource($salesForecast->getAsm()));
            $salesForecastClassed[$key][] = $salesForecast;
        }

        foreach ($salesForecastClassed as $key => $salesForecasts) {
            $keys = explode('-', $key);
            /** @var Location $location */
            $location = $this->iriConverter->getResourceFromIri($keys[0]);

            /** @var People $asm */
            $asm = $this->iriConverter->getResourceFromIri($keys[1]);
            $this->notifier->sendDelinquent($salesForecasts, $asm, $location);
        }

        return 0;
    }
}

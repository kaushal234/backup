<?php

declare(strict_types=1);

namespace App\DataProcessor\Sales\SalesForecast;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Sales\SalesForecast\SalesForecastLinkTo;
use App\Entity\Sales\MasterSalesForecast;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @template T
 */
class SalesForecastLinkToDataProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param SalesForecastLinkTo $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $repository = $this->entityManager->getRepository(SalesForecast::class);
        $salesForecast = $repository->find($uriVariables['id']);
        $masterSalesForecast = $data->salesForecastToLink->getMasterSalesForecast();
        if (!$masterSalesForecast instanceof MasterSalesForecast) {
            throw new UnprocessableEntityHttpException();
        }

        $masterSalesForecast->addSalesForecast($salesForecast);

        $this->entityManager->persist($salesForecast);
        $this->entityManager->flush();

        return $salesForecast;
    }
}

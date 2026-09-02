<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Directory\Location;
use App\Manager\Finance\ManufacturingMarginSynthesisManager;
use LegacyBundle\Manager\ManufacturingMarginManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ManufacturingMarginSynthesisDataProvider implements ProviderInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            ManufacturingMarginSynthesisManager::class,
            ManufacturingMarginManager::class,
            IriConverterInterface::class,
        ];
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var string|null $dateFrom */
        $dateFrom = $context['filters']['exportDate']['after'] ?? null;
        /** @var string|null $dateTo */
        $dateTo = $context['filters']['exportDate']['before'] ?? null;
        /** @var string|null $factoryIri */
        $factoryIri = $context['filters']['factory'] ?? null;
        $factory = null;
        if (null !== $factoryIri) {
            $factory = $this->container->get(IriConverterInterface::class)->getResourceFromIri($factoryIri);
        }

        if (null === $dateFrom || null === $dateTo || !$factory instanceof Location) {
            throw new BadRequestHttpException('Date and factory are mandatory filters for this route.');
        }

        $manufacturingMarginSynthesises = $this->container->get(ManufacturingMarginSynthesisManager::class)->getManufacturingMarginSynthesisForPeriod($dateFrom, $dateTo, $factory->getId());

        $salesOrderLines = $this->container->get(ManufacturingMarginManager::class)->getSalesOrderLineInformationByFinanceFamily($dateFrom, $dateTo, $factory->getName());
        $salesOrderLinesIndexed = [];

        foreach ($salesOrderLines as $salesOrderLine) {
            $salesOrderLinesIndexed[$salesOrderLine['financeFamily']] = $salesOrderLine;
        }

        foreach ($manufacturingMarginSynthesises as $manufacturingMarginSynthesis) {
            if ($salesOrderLine = $salesOrderLinesIndexed[$manufacturingMarginSynthesis->getFinanceFamily()] ?? false) {
                $manufacturingMarginSynthesis->setAverageProjectedDirectMargin((int) $salesOrderLine['average_projected_direct_margin']);
                $manufacturingMarginSynthesis->setAverageFactoryDiscount((int) $salesOrderLine['average_factory_discount']);
            }
        }

        return $manufacturingMarginSynthesises;
    }
}

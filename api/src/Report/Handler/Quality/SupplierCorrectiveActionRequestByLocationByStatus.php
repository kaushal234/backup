<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Directory\Location;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;

class SupplierCorrectiveActionRequestByLocationByStatus implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    private readonly IriConverterInterface $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SupplierCorrectiveActionRequest::class !== $resourceClass || 'status' !== $y || 'factory.name' !== $x) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $queriesBuilder->getMainQueryBuilder()
            ->addSelect('x_0.id AS factory_id');

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))()
        );

        $provider->setMetadataExtractor(function ($results) {
            $xIris = [];

            $locationIri = $this->iriConverter->getIriFromResource(Location::class, UrlGeneratorInterface::ABS_PATH, new GetCollection());

            foreach ($results as $result) {
                $xIris[$result['x']] = \sprintf('%s/%s', $locationIri, $result['factory_id']);
            }

            return [
                self::METADATA_IRIS_X_KEY => $xIris,
            ];
        });

        return $provider;
    }
}

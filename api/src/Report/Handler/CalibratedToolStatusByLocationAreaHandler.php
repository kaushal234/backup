<?php

declare(strict_types=1);

namespace App\Report\Handler;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Quality\CalibratedTools\Tool;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CalibratedToolStatusByLocationAreaHandler implements ReportHandlerInterface
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
        if (Tool::class !== $resourceClass || 'locationArea.name' !== $x || 'status' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        if (isset($options['location'])) {
            try {
                /** @var Location $location */
                $location = $this->iriConverter->getResourceFromIri($options['location']);
            } catch (\InvalidArgumentException $invalidArgumentException) {
                throw new NotFoundHttpException(\sprintf('Location %s not found', $options['location']), $invalidArgumentException);
            }

            if (!$location->getCapability()->isFactory()) {
                throw new BadRequestHttpException(\sprintf('Location %s is not a factory', $location->getName()));
            }

            $queriesBuilder->getMainQueryBuilder()
                ->join('o.locationArea', 'la')
                ->andWhere('la.factory = :location')
                ->setParameter('location', $location)
            ;

            $queriesBuilder->getXQueryBuilder()
                ->andWhere('o.factory = :location')
                ->setParameter('location', $location)
            ;
        }

        return new ReportDataProvider(
            (new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))(),
            (new QueryBuilderExtractor($queriesBuilder->getXQueryBuilder()))(),
            (new QueryBuilderExtractor($queriesBuilder->getYQueryBuilder()))()
        );
    }
}

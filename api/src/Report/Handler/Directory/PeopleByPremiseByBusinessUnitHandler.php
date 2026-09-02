<?php

declare(strict_types=1);

namespace App\Report\Handler\Directory;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Entity\Directory\Premise;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

class PeopleByPremiseByBusinessUnitHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (People::class !== $resourceClass || 'businessUnit.name' !== $y || 'premise.name' !== $x) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $queriesBuilder->getMainQueryBuilder()
            ->addSelect('x_0.id AS premise_id')
            ->addSelect('y_0.id AS business_unit_id')
            ->where('o.hidden = :hidden ')
            ->andWhere('o.disabled = :disabled ')
            ->andWhere('x_0.archived = :archived ')
            ->setParameters(new ArrayCollection([
                new Parameter('hidden', false),
                new Parameter('disabled', false),
                new Parameter('archived', false),
            ]));

        $queriesBuilder->getXQueryBuilder()->andWhere('o.archived = :archived')->setParameter('archived', false);

        if ($options['type'] ?? null) {
            $queriesBuilder->getMainQueryBuilder()->leftJoin('x_0.tags', 't');
            $queriesBuilder->getMainQueryBuilder()->andWhere('t.id = :id')->setParameter('id', $options['type']);
        }

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))(),
            null,
            (new QueryBuilderExtractor($queriesBuilder->getYQueryBuilder()))()
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Premise::class, 'premise_id')
            ->setY(BusinessUnit::class, 'business_unit_id')
            ->generate()
        );
    }
}

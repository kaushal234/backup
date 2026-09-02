<?php

declare(strict_types=1);

namespace App\Report\Handler;

use App\Entity\Directory\People;
use App\Entity\DMS;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

class DMSStatusByBusinessUnitHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (DMS::class !== $resourceClass || 'owner.businessUnit.name' !== $x || 'status' !== $y || 'Sales Material' !== ($options['type'] ?? null)) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $queriesBuilder->getMainQueryBuilder()
            ->where('o.type = :type ')
            ->andWhere('o.status != :archive')
            ->setParameters(new ArrayCollection([
                new Parameter('type', $options['type']),
                new Parameter('archive', 'ARCHIVE'),
            ]));

        return new ReportDataProvider((new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))());
    }

    public function isGranted(?object $user = null): bool
    {
        // We need to authorize a null user because this report is accessible from a command
        return $user instanceof People || null === $user;
    }
}

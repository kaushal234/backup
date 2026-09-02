<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\MaterialRequirementsPlanning;

use App\CQRS\Query\MaterialRequirementsPlanning\FindAllMaterialRequirementsPlanningsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\MaterialRequirementsPlanning;
use Psl\Collection\AccessibleCollectionInterface;

final class FindAllMaterialRequirementsPlanningsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<int, MaterialRequirementsPlanning>
     */
    public function __invoke(FindAllMaterialRequirementsPlanningsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(MaterialRequirementsPlanning::class);
    }
}

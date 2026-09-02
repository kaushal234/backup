<?php

declare(strict_types=1);

namespace App\CQRS\Query\MaterialRequirementsPlanning;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\MaterialRequirementsPlanning;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<MaterialRequirementsPlanning>>
 */
final class FindAllMaterialRequirementsPlanningsQuery implements QueryInterface
{
}

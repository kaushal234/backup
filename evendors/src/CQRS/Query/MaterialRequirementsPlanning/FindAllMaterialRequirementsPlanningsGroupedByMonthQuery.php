<?php

declare(strict_types=1);

namespace App\CQRS\Query\MaterialRequirementsPlanning;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\MaterialRequirementsPlanning;

/**
 * @implements QueryInterface<array<string, list<MaterialRequirementsPlanning>>>
 */
final class FindAllMaterialRequirementsPlanningsGroupedByMonthQuery implements QueryInterface
{
}

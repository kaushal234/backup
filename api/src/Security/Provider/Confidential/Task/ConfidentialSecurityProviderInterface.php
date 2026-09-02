<?php

declare(strict_types=1);

namespace App\Security\Provider\Confidential\Task;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('task.security.provider')]
interface ConfidentialSecurityProviderInterface
{
    public function provideOrStatement(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $moduleAlias): ?Orx;

    public function isGranted(object $entity): bool;
}

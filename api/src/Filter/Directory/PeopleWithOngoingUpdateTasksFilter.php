<?php

declare(strict_types=1);

namespace App\Filter\Directory;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use Doctrine\ORM\QueryBuilder;

/**
 * Restricts a /people collection based on the existence of ongoing
 * update tasks (UpdateTask.done = false). Activated via
 * ?withOngoingUpdateTasks=true|false; absent means no filter.
 */
final class PeopleWithOngoingUpdateTasksFilter extends AbstractFilter
{
    final public const PROPERTY = 'withOngoingUpdateTasks';

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY => [
                'property' => self::PROPERTY,
                'type' => 'boolean',
                'required' => false,
                'description' => 'When true, only return people with at least one ongoing update task. When false, only return people without any.',
            ],
        ];
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (self::PROPERTY !== $property || People::class !== $resourceClass) {
            return;
        }

        $bool = filter_var($value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE);
        if (null === $bool) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder();
        $subQuery
            ->select('1')
            ->from(UpdateTask::class, 'updateTask')
            ->where('updateTask.user = '.$alias.'.id')
            ->andWhere('updateTask.done = false');

        $existsExpr = $queryBuilder->expr()->exists($subQuery->getDQL());

        $queryBuilder->andWhere($bool ? $existsExpr : $queryBuilder->expr()->not($existsExpr));
    }
}

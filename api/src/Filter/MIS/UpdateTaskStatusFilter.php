<?php

declare(strict_types=1);

namespace App\Filter\MIS;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use App\Entity\Module\ThirdPartyApp\UpdateTaskStatus;
use Doctrine\ORM\QueryBuilder;

final class UpdateTaskStatusFilter extends AbstractFilter
{
    final public const PROPERTY = 'status';

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY => [
                'property' => self::PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (UpdateTask::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to UpdateTask resource');
        }

        if (self::PROPERTY !== $property) {
            return;
        }

        match (UpdateTaskStatus::tryFrom($value)) {
            UpdateTaskStatus::InProgress => $queryBuilder->andWhere('o.done = 0'),
            UpdateTaskStatus::Confirmed => $queryBuilder->andWhere('o.done = 1 AND o.confirmed = 1'),
            UpdateTaskStatus::Denied => $queryBuilder->andWhere('o.done = 1 AND o.confirmed = 0'),
            default => throw new \InvalidArgumentException('Not a valid update task status'),
        };
    }
}

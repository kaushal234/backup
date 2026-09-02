<?php

declare(strict_types=1);

namespace App\Filter\Service;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\QueryBuilder;

class TechnicianOnCallLateFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_USED_PROPERTY = 'late';

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $isLate = $context['filters'][static::FILTER_USED_PROPERTY] ?? null;

        if (!$isLate || 'false' === $isLate) {
            return;
        }

        if (TechnicianOnCall::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the TechnicianOnCall resource');
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // TOC updatedAt is updated for each action or comment on the TOC. So we can use it to determine if the TOC is late.
        $queryBuilder
            ->andWhere(\sprintf('%s.updatedAt < :fiveDaysAgo', $rootAlias))
            ->andWhere($queryBuilder->expr()->in(\sprintf('%s.status', $rootAlias), ':open_statuses'))
            ->setParameter('fiveDaysAgo', (new \DateTime())->modify('-5 days'))
            ->setParameter('open_statuses', TechnicianOnCall::OPENED_STATUSES)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_USED_PROPERTY => [
                'property' => static::FILTER_USED_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}

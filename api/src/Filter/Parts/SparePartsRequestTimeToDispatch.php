<?php

declare(strict_types=1);

namespace App\Filter\Parts;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Parts\SparePartsRequest;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SparePartsRequestTimeToDispatch implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_USED_PROPERTY = 'shippedDelay';

    public function __construct(
        protected RequestStack $requestStack,
        protected ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (SparePartsRequest::class !== $resourceClass && !is_subclass_of($resourceClass, SparePartsRequest::class)) {
            throw new \Exception('This filter is restricted to the SparePartsRequest resource');
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        $delay = $request->query->get(static::FILTER_USED_PROPERTY);

        if (!is_numeric($delay) || $delay < 0) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere($queryBuilder->expr()->in(\sprintf('%s.status', $rootAlias), ':spr_statuses'))
            ->setParameter('spr_statuses', [SparePartsRequest::STATUS_SHIPPED, SparePartsRequest::STATUS_CLOSED])
        ;

        if ($delay > 5) {
            $queryBuilder->andWhere(\sprintf('DATE_DIFF(%1$s.shippingDate, %1$s.createdAt) > 5', $rootAlias));
        } else {
            $queryBuilder
                ->andWhere(\sprintf('DATE_DIFF(%1$s.shippingDate, %1$s.createdAt) = :delay', $rootAlias))
                ->setParameter('delay', (int) $delay)
            ;
        }
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

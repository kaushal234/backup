<?php

declare(strict_types=1);

namespace App\Filter\Support\EquipmentRecord;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EquipmentRecordShippedFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_NOT_SHIPPED = 'notShipped';

    private readonly RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(self::FILTER_NOT_SHIPPED) || !\in_array($request->query->get(static::FILTER_NOT_SHIPPED), [true, 'true', '1'], true)) {
            return;
        }
        $queryBuilder->andWhere(\sprintf('%s.dateShipped IS NULL', $queryBuilder->getRootAliases()[0]));
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_NOT_SHIPPED => [
                'property' => static::FILTER_NOT_SHIPPED,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filter\Customer;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Demo;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerDemoUsedFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_USED_PROPERTY = 'has_demo';

    protected RequestStack $requestStack;

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

        if (!$request->query->has(static::FILTER_USED_PROPERTY)) {
            return;
        }

        $value = $request->query->get(static::FILTER_USED_PROPERTY);

        if (Customer::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Customer resource');
        }

        if (\in_array($value, [true, 'true', '1'], true)) {
            $queryBuilder
                ->leftJoin(Demo::class, 'd', Join::WITH, 'o.id = d.customer')
                ->andWhere($queryBuilder->expr()->isNotNull('d.status'));
        }

        if (\in_array($value, [false, 'false', '0'], true)) {
            $queryBuilder
                ->leftJoin(Demo::class, 'd', Join::WITH, 'o.id = d.customer')
                ->andWhere($queryBuilder->expr()->isNull('d.status'));
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
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}

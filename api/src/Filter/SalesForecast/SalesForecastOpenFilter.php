<?php

declare(strict_types=1);

namespace App\Filter\SalesForecast;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SalesForecastOpenFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_OPEN_PROPERTY = 'open';

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

        if (!$request->query->has(static::FILTER_OPEN_PROPERTY)) {
            return;
        }

        $value = $request->query->get(static::FILTER_OPEN_PROPERTY);

        if (SalesForecast::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Sales Forecasts resource');
        }

        if (\in_array($value, [true, 'true', '1'], true)) {
            $orStatements = $queryBuilder->expr()->orX();

            $orStatements->add($queryBuilder->expr()->in('o.status', ':open_statuses'));
            $orStatements->add($queryBuilder->expr()->gt('o.closedAt', ':one_month_ago'));

            $queryBuilder->andWhere($orStatements);

            $queryBuilder->setParameter('open_statuses', SalesForecast::OPEN_STATUSES);
            $queryBuilder->setParameter('one_month_ago', new \DateTime('1 month ago'));
        }

        if (\in_array($value, [false, 'false', '0'], true)) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('o.status', ':open_statuses'));

            $queryBuilder->setParameter('open_statuses', SalesForecast::OPEN_STATUSES);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_OPEN_PROPERTY => [
                'property' => static::FILTER_OPEN_PROPERTY,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}

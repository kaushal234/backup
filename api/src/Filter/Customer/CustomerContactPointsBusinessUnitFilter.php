<?php

declare(strict_types=1);

namespace App\Filter\Customer;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\Customer;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerContactPointsBusinessUnitFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_USED_PROPERTY = 'contactPointsBusinessUnits';

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

        $queryBuilder
            ->innerJoin('o.mainSalesRepresentative', 'mainContactPoint')
            ->innerJoin('mainContactPoint.asm', 'mainAsm')
            ->innerJoin('mainAsm.businessUnit', 'mainContactPointBu')
            ->innerJoin('mainContactPointBu.location', 'mainContactPointLocation')
            ->innerJoin('o.secondarySalesRepresentatives', 'secondaryContactPoints')
            ->innerJoin('secondaryContactPoints.asm', 'secondaryAsm')
            ->innerJoin('secondaryAsm.businessUnit', 'secondaryContactPointsBu')
            ->innerJoin('secondaryContactPointsBu.location', 'secondaryContactPointsLocation')
            ->andWhere('mainContactPointLocation = :value OR secondaryContactPointsLocation = :value')
            ->setParameter('value', $value)
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
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}

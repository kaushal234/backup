<?php

declare(strict_types=1);

namespace App\Filter\SalesForecast;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SalesForecastCustomerFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_CUSTOMER_PROPERTY = 'customer';

    protected IriConverterInterface $iriConverter;

    protected RequestStack $requestStack;

    public function __construct(IriConverterInterface $iriConverter, RequestStack $requestStack)
    {
        $this->iriConverter = $iriConverter;
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

        if (!$request->query->has(static::FILTER_CUSTOMER_PROPERTY)) {
            return;
        }

        $value = $request->query->get(static::FILTER_CUSTOMER_PROPERTY);

        if (SalesForecast::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Sales Forecasts resource');
        }

        $customer = $this->iriConverter->getResourceFromIri($value);

        $orStatements = $queryBuilder->expr()->orX();

        $orStatements->add($queryBuilder->expr()->eq('o.buyer', ':customer'));
        $orStatements->add($queryBuilder->expr()->eq('o.endUser', ':customer'));
        $orStatements->add($queryBuilder->expr()->eq('o.thirdParty', ':customer'));

        $queryBuilder->setParameter('customer', $customer);

        $queryBuilder->andWhere($orStatements);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_CUSTOMER_PROPERTY => [
                'property' => static::FILTER_CUSTOMER_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}

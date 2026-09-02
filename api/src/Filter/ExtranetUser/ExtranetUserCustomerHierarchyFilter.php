<?php

declare(strict_types=1);

namespace App\Filter\ExtranetUser;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ExtranetUserCustomerHierarchyFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_CUSTOMER_HIERARCHY_PROPERTY = 'customer_hierarchy';

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

        if (!$request->query->has(static::FILTER_CUSTOMER_HIERARCHY_PROPERTY)) {
            return;
        }

        $value = $request->query->get(static::FILTER_CUSTOMER_HIERARCHY_PROPERTY);

        if (ExtranetUser::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the ExtranetUser resource');
        }

        /** @var Customer $customer */
        $customer = $this->iriConverter->getResourceFromIri($value);

        for ($i = 0; $i < 5; ++$i) {
            if (null === $customer->getParentCustomer()) {
                continue;
            }

            $customer = $customer->getParentCustomer();
        }

        $orStatements = $queryBuilder->expr()->orX();
        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->leftJoin(\sprintf('%s.extranetUserProfile', $alias), 'xu_profile')
            ->leftJoin('xu_profile.customer', 'extranet_user_customer_level_1')
        ;
        $orStatements->add($queryBuilder->expr()->eq('extranet_user_customer_level_1.id', $customer->getId()));

        for ($i = 2; $i <= 5; ++$i) {
            $condition = \sprintf('extranet_user_customer_level_%s.parentCustomer', $i - 1).\sprintf('= extranet_user_customer_level_%s.id', $i);
            $queryBuilder->leftJoin(Customer::class, \sprintf('extranet_user_customer_level_%s', $i), Join::WITH, $condition);
            $orStatements->add($queryBuilder->expr()->eq(\sprintf('extranet_user_customer_level_%s', $i), $customer->getId()));
        }
        $queryBuilder->andWhere($orStatements);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_CUSTOMER_HIERARCHY_PROPERTY => [
                'property' => static::FILTER_CUSTOMER_HIERARCHY_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}

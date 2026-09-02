<?php

declare(strict_types=1);

namespace App\Filter\Purchasing;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class VendorWarrantyClaimOpenFilter implements FilterInterface
{
    final public const FILTER_OPEN_PROPERTY = 'open';

    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

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

        if (VendorWarrantyClaim::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Vendor Warranty Claim resource');
        }

        if (\in_array($value, [false, 'false', '0'], true)) {
            return;
        }

        if (\in_array($value, [true, 'true', '1'], true)) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $statusAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'status', Join::LEFT_JOIN);

            $queryBuilder->where($queryBuilder->expr()->notIn(\sprintf('%s.name', $statusAlias), ':statuses'));
            $queryBuilder->setParameter('statuses', VendorWarrantyClaimStatus::CLOSED_STATUSES);
        }
    }

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

<?php

declare(strict_types=1);

namespace App\Filter\Purchasing;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\WCVendorWarrantyClaim;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class WCVendorWarrantyClaimIdFilter implements FilterInterface
{
    final public const FILTER_PROPERTY = 'warrantyClaimId';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (VendorWarrantyClaim::class !== $resourceClass) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(self::FILTER_PROPERTY)) {
            return;
        }

        $value = $request->query->get(self::FILTER_PROPERTY);

        if (!\is_scalar($value) || !ctype_digit((string) $value)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere(\sprintf('%s INSTANCE OF %s', $rootAlias, WCVendorWarrantyClaim::class));

        $wcAlias = $queryNameGenerator->generateJoinAlias('wc');
        $subQb = $queryBuilder->getEntityManager()->createQueryBuilder();

        $subQb
            ->select('1')
            ->from(WCVendorWarrantyClaim::class, $wcAlias)
            ->where(\sprintf('%s.id = %s.id', $wcAlias, $rootAlias))
            ->andWhere(\sprintf('%s.warrantyClaimId = :warrantyClaimId', $wcAlias));

        $queryBuilder
            ->andWhere(\sprintf('EXISTS (%s)', $subQb->getDQL()))
            ->setParameter('warrantyClaimId', (int) $value);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::FILTER_PROPERTY => [
                'property' => self::FILTER_PROPERTY,
                'type' => 'int',
                'required' => false,
            ],
        ];
    }
}

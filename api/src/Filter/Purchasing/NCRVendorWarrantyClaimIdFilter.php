<?php

declare(strict_types=1);

namespace App\Filter\Purchasing;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaim;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class NCRVendorWarrantyClaimIdFilter implements FilterInterface
{
    final public const FILTER_PROPERTY = 'nonConformityId';

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

        $nonConformityId = (int) $value;

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Only NCR subtype
        $queryBuilder->andWhere(\sprintf('%s INSTANCE OF %s', $rootAlias, NCRVendorWarrantyClaim::class));

        $ncrAlias = $queryNameGenerator->generateJoinAlias('ncr');
        $subQb = $queryBuilder->getEntityManager()->createQueryBuilder();

        $subQb
            ->select('1')
            ->from(NCRVendorWarrantyClaim::class, $ncrAlias)
            ->where(\sprintf('%s.id = %s.id', $ncrAlias, $rootAlias))
            ->andWhere(\sprintf('%s.nonConformity = :nonConformityId', $ncrAlias));

        $queryBuilder
            ->andWhere(\sprintf('EXISTS (%s)', $subQb->getDQL()))
            ->setParameter('nonConformityId', $nonConformityId);
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

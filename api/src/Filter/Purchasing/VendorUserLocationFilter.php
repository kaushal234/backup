<?php

declare(strict_types=1);

namespace App\Filter\Purchasing;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\Location;
use App\Entity\Purchasing\Supplier\SupplierLocation;
use App\Entity\Purchasing\VendorUser;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class VendorUserLocationFilter implements FilterInterface
{
    final public const FILTER_VENDOR_USER_LOCATIONS_PROPERTY = 'vendorUserLocations';

    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack
    ) {
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof VendorUser) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(static::FILTER_VENDOR_USER_LOCATIONS_PROPERTY)) {
            return;
        }

        if (Location::class !== $resourceClass) {
            return;
        }

        $value = $request->query->get(static::FILTER_VENDOR_USER_LOCATIONS_PROPERTY);
        if (\in_array($value, [false, 'false', '0'], true)) {
            return;
        }

        $aliases = $queryBuilder->getRootAliases();
        if (empty($aliases)) {
            return;
        }

        $rootAlias = $aliases[0];
        $supplierCodes = $user->getBusinessPartnerCodes();

        if ([] === $supplierCodes) {
            $queryBuilder->andWhere('1 = 0');

            return;
        }

        $em = $queryBuilder->getEntityManager();

        $sub = $em->createQueryBuilder()
            ->select('1')
            ->from(SupplierLocation::class, 'sl')
            ->join('sl.supplier', 's')
            ->where(\sprintf('sl.location = %s', $rootAlias))
            ->andWhere('s.code IN (:vu_supplier_codes)');

        $queryBuilder
            ->andWhere($queryBuilder->expr()->exists($sub->getDQL()))
            ->setParameter('vu_supplier_codes', $supplierCodes);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_VENDOR_USER_LOCATIONS_PROPERTY => [
                'property' => static::FILTER_VENDOR_USER_LOCATIONS_PROPERTY,
                'type' => 'bool',
                'required' => false,
                'swagger' => [
                    'description' => 'When true, get locations to those linked to the connected VendorUser.',
                ],
            ],
        ];
    }
}

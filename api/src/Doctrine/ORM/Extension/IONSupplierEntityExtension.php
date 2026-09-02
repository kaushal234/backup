<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Entity\Purchasing\WCVendorWarrantyClaim;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final class IONSupplierEntityExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        if (!\in_array($resourceClass, [SupplierCorrectiveActionRequest::class, VendorWarrantyClaim::class, NCRVendorWarrantyClaim::class, WCVendorWarrantyClaim::class], true)) {
            return;
        }

        $security = $this->container->get(Security::class);
        if (null === ($user = $security->getUser()) || !$user instanceof VendorUser) {
            return;
        }

        $queryBuilder
            ->andWhere($queryBuilder->expr()->in(\sprintf('%s.supplierNumber', $queryBuilder->getRootAliases()[0]), ':businessPartnerCodes'))
            ->setParameter('businessPartnerCodes', $user->getBusinessPartnerCodes())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (!\in_array($resourceClass, [SupplierCorrectiveActionRequest::class, VendorWarrantyClaim::class, NCRVendorWarrantyClaim::class, WCVendorWarrantyClaim::class], true) || (!$operation instanceof Get && !$operation instanceof GetCollection)) {
            return;
        }

        $security = $this->container->get(Security::class);
        if (null === ($user = $security->getUser()) || !$user instanceof VendorUser) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere($queryBuilder->expr()->in(\sprintf('%s.supplierNumber', $rootAlias), ':businessPartnerCodes'))
            ->setParameter('businessPartnerCodes', $user->getBusinessPartnerCodes())
        ;

        if (VendorWarrantyClaim::class === $resourceClass) {
            $queryBuilder->leftJoin(VendorWarrantyClaimStatus::class, 'status', Join::WITH, \sprintf('%s.status = status.id', $rootAlias));
            $queryBuilder->andWhere($queryBuilder->expr()->in('status.name', [
                VendorWarrantyClaimStatus::VENDOR_TO_RESPOND,
                VendorWarrantyClaimStatus::REVIEW_VENDOR_RESPONSE,
                VendorWarrantyClaimStatus::CREATE_PO,
                VendorWarrantyClaimStatus::SHIP_TO_VENDOR,
                VendorWarrantyClaimStatus::ISSUE_CREDIT_NOTE,
                VendorWarrantyClaimStatus::REC_FROM_VENDOR,
                VendorWarrantyClaimStatus::ISSUE_DEBIT_NOTE,
                VendorWarrantyClaimStatus::CLOSED_RESOLVED,
                VendorWarrantyClaimStatus::CLOSED_LOW_VALUE,
                VendorWarrantyClaimStatus::CLOSED_VENDOR_REJECTED,
            ]));
        }
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}

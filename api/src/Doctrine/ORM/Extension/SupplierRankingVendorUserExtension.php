<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Entity\Purchasing\VendorUser;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SupplierRankingVendorUserExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->preFilteredForVendorUser($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }

    protected function preFilteredForVendorUser(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): void
    {
        if (SupplierRanking::class !== $resourceClass) {
            return;
        }

        if (null === $user = $this->container->get(Security::class)->getUser()) {
            return;
        }
        if (!$user instanceof VendorUser) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->innerJoin(\sprintf('%s.supplier', $rootAlias), 'supplier');
        $queryBuilder->andWhere('supplier.code IN (:businessPartners)');
        $queryBuilder->setParameter('businessPartners', $user->getBusinessPartnerCodes());
    }
}

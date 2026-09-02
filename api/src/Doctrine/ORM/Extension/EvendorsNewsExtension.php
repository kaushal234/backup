<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\Materials\EvendorsNews;
use App\Entity\Purchasing\VendorUser;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class EvendorsNewsExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        if (EvendorsNews::class !== $resourceClass) {
            return;
        }

        if (null === ($user = $this->container->get(Security::class)->getUser()) || !$user instanceof VendorUser) {
            return;
        }

        $this->provideQueryBuilder($queryBuilder, $user);
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (EvendorsNews::class !== $resourceClass || !$operation instanceof GetCollection) {
            return;
        }

        if (null === ($user = $this->container->get(Security::class)->getUser()) || !$user instanceof VendorUser) {
            return;
        }

        $this->provideQueryBuilder($queryBuilder, $user);
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }

    protected function provideQueryBuilder(QueryBuilder $queryBuilder, VendorUser $user)
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->leftJoin("$rootAlias.factories", 'location')
            ->andWhere($queryBuilder->expr()->in('location.erp', ':erp'))
            ->setParameter('erp', $user->getErpCodes())
            ->andWhere("$rootAlias.publishedAt <= :currentDateTime")
            ->andWhere("$rootAlias.unpublishedAt >= :currentDateTime")
            ->setParameter('currentDateTime', new \DateTime())
        ;
    }
}

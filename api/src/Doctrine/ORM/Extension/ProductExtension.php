<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\Product;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final readonly class ProductExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface, ServiceSubscriberInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }

    private function addWhere(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
    ): void {
        if (Product::class !== $resourceClass) {
            return;
        }

        $user = $this->container->get(Security::class)->getUser();
        if (!$user instanceof ExtranetUser) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $productFamilyAlias = $queryNameGenerator->generateJoinAlias('productFamilies');
        $productTypeAlias = $queryNameGenerator->generateJoinAlias('productType');

        $queryBuilder
            ->leftJoin(\sprintf('%s.family', $rootAlias), $productFamilyAlias)
            ->leftJoin(\sprintf('%s.productType', $productFamilyAlias), $productTypeAlias)
        ;

        $orStatementsProductFamily = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->eq(\sprintf('%s.publicForTLD', $productFamilyAlias), ':true'),
            $queryBuilder->expr()->eq(\sprintf('%s.publicForAerospecialties', $productFamilyAlias), ':true'),
            $queryBuilder->expr()->eq(\sprintf('%s.publicForSAS', $productFamilyAlias), ':true')
        );

        $orStatements = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->eq(\sprintf('%s.publicForTLD', $productTypeAlias), ':true'),
            $queryBuilder->expr()->eq(\sprintf('%s.publicForAerospecialties', $productTypeAlias), ':true'),
            $queryBuilder->expr()->eq(\sprintf('%s.publicForSAS', $productTypeAlias), ':true')
        );
        $queryBuilder
            ->andWhere($orStatements)
            ->andWhere($orStatementsProductFamily)
            ->andWhere($queryBuilder->expr()->eq(\sprintf('%s.hidden', $productFamilyAlias), ':false'))
            ->andWhere($queryBuilder->expr()->eq(\sprintf('%s.hidden', $rootAlias), ':false'))
            ->setParameter('true', true)
            ->setParameter('false', false)
        ;
    }
}

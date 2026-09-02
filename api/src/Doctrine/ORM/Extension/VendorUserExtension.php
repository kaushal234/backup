<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\VendorUser;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class VendorUserExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (VendorUser::class !== $resourceClass) {
            return;
        }

        if (null === $user = $this->container->get(Security::class)->getUser()) {
            return;
        }

        if (!$user instanceof VendorUser) {
            return;
        }

        $queryBuilder
            ->andWhere(\sprintf('%s.id = :current_user', $queryBuilder->getRootAliases()[0]))
            ->setParameter('current_user', $user->getId())
        ;
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        if (VendorUser::class !== $resourceClass) {
            return;
        }

        $security = $this->container->get(Security::class);
        if (null !== ($user = $security->getUser()) && $security->isGranted('ACCESS_VENDOR_USER')) {
            $queryBuilder->andWhere(\sprintf('%s.id = :id', $queryBuilder->getRootAliases()[0]));
            $queryBuilder->setParameter('id', $user->getId());
        }
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}

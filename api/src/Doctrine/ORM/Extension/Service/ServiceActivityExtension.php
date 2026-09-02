<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension\Service;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\ServiceActivity;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final readonly class ServiceActivityExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface, ServiceSubscriberInterface
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
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }

    /**
     * Hide the "Commissioning" service activity from extranet users.
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (ServiceActivity::class !== $resourceClass) {
            return;
        }

        $user = $this->container->get(Security::class)->getUser();
        if (!$user instanceof ExtranetUser) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(\sprintf('%s.name != :excludedServiceActivity', $rootAlias))
            ->setParameter('excludedServiceActivity', ServiceActivity::COMMISSIONING);
    }
}

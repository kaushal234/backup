<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Support\MaintenanceContract;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final readonly class MaintenanceContractExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (MaintenanceContract::class !== $resourceClass) {
            return;
        }

        $user = $this->container->get(Security::class)->getUser();
        $rootAlias = $queryBuilder->getRootAliases()[0];
        if ($user instanceof ExtranetUser) {
            $endUserAlias = $queryNameGenerator->generateJoinAlias('endUserRepresentatives');
            $buyerAlias = $queryNameGenerator->generateJoinAlias('buyerRepresentatives');
            $queryBuilder
                ->leftJoin(\sprintf('%s.endUserRepresentatives', $rootAlias), $endUserAlias)
                ->leftJoin(\sprintf('%s.buyerRepresentatives', $rootAlias), $buyerAlias)
                ->andWhere(\sprintf('%s.id = :current_extranet_user OR %s.id = :current_extranet_user', $endUserAlias, $buyerAlias))
                ->setParameter('current_extranet_user', $user->getId())
            ;
        }
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}

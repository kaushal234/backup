<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final readonly class ExtranetUserAclExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
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
        if (ExtranetUserAcl::class !== $resourceClass) {
            return;
        }

        $user = $this->container->get(Security::class)->getUser();
        if ($user instanceof ExtranetUser) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->andWhere(\sprintf('%s.extranetUser = :current_extranet_user', $rootAlias))
                ->setParameter('current_extranet_user', $user)
            ;
        }
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}

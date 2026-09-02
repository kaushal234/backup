<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\UserSetting;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class UserSettingExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (UserSetting::class !== $resourceClass) {
            return;
        }

        if (null === $user = $this->container->get(Security::class)->getUser()) {
            return;
        }

        if (!$user instanceof People) {
            $queryBuilder->where('1=0');

            return;
        }

        $queryBuilder
           ->andWhere(\sprintf('%s.user = :current_user', $queryBuilder->getRootAliases()[0]))
           ->setParameter('current_user', $user)
        ;
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}

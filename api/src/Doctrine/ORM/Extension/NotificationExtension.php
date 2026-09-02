<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Common\Notification\Notification;
use App\Entity\Directory\People;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class NotificationExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (Notification::class !== $resourceClass) {
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
           ->andWhere(\sprintf('%s.people = :current_user', $queryBuilder->getRootAliases()[0]))
           ->setParameter('current_user', $user)
        ;
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}

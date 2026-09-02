<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension\AI;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\AI\AILog;
use App\Entity\Directory\People;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

readonly class AILogExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if ('get_ai_logs_history' === $operation?->getName()) {
            return;
        }

        $this->applyFilters($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->applyFilters($queryBuilder, $resourceClass);
    }

    private function applyFilters(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (AILog::class !== $resourceClass) {
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
}

<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Entity\User;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class MarketIntelligenceExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        if (MarketIntelligence::class !== $resourceClass) {
            return;
        }

        /** @var User|null $user */
        $user = $this->container->get(Security::class)->getUser();

        if (!$user instanceof People || null === $user->getPosition()) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $linkedAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'marketIntelligencesLinked', Join::LEFT_JOIN);
        $queryBuilder
            ->leftJoin(\sprintf('%s.positionLevels', $linkedAlias), 'positionLevelLinked')
        ;

        $orStatementsLinked = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->eq('positionLevelLinked', ':userPositionLevels'),
            $queryBuilder->expr()->isNull('positionLevelLinked'),
            $queryBuilder->expr()->eq(\sprintf('%s.poster', $linkedAlias), ':user'),
        );

        $queryBuilder
            ->andWhere($orStatementsLinked)
            ->setParameter('userPositionLevels', $user->getPosition()->getLevel())
            ->setParameter('user', $user);
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (MarketIntelligence::class !== $resourceClass) {
            return;
        }

        /** @var User|null $user */
        $user = $this->container->get(Security::class)->getUser();

        if (!$user instanceof People || null === $user->getPosition()) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $linkedAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'marketIntelligencesLinked', Join::LEFT_JOIN);
        $queryBuilder
            ->leftJoin(\sprintf('%s.positionLevels', $linkedAlias), 'positionLevelLinked')
            ->leftJoin(\sprintf('%s.positionLevels', $rootAlias), 'positionLevel')
        ;

        $orStatementsLinked = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->eq('positionLevelLinked', ':userPositionLevels'),
            $queryBuilder->expr()->isNull('positionLevelLinked'),
            $queryBuilder->expr()->eq(\sprintf('%s.poster', $linkedAlias), ':user'),
        );

        $orStatements = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->eq('positionLevel', ':userPositionLevels'),
            $queryBuilder->expr()->isNull('positionLevel'),
            $queryBuilder->expr()->eq(\sprintf('%s.poster', $rootAlias), ':user'),
        );

        $queryBuilder
            ->andWhere($orStatements)
            ->andWhere($orStatementsLinked)
            ->setParameter('userPositionLevels', $user->getPosition()->getLevel())
            ->setParameter('user', $user);
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}

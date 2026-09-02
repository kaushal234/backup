<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\MIS\Project\Project;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ProjectExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->constructQueryBuilder($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->constructQueryBuilder($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }

    private function constructQueryBuilder(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): void
    {
        if (Project::class !== $resourceClass) {
            return;
        }

        $security = $this->container->get(Security::class);
        $user = $security->getUser();

        if ($security->isGranted('FEATURE_MIS_PROJECT_READ_CONFIDENTIAL')) {
            return;
        }
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $moduleKeyUsersAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'moduleKeyUsers', Join::LEFT_JOIN);
        $misMembersAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'misMembers', Join::LEFT_JOIN);

        $orStatements = $queryBuilder->expr()->orX();
        $orStatements->add(\sprintf('%s.confidential = :false', $rootAlias));

        $andX = $queryBuilder->expr()->andX();
        $andX->add(\sprintf('%s.confidential = :true', $rootAlias));

        $orStatementUser = $queryBuilder->expr()->orX();
        $orStatementUser->add(\sprintf('%s.projectManager = :user', $rootAlias));
        $orStatementUser->add(\sprintf('%s.misOwner = :user', $rootAlias));
        $orStatementUser->add(\sprintf('%s.id = :user', $moduleKeyUsersAlias));
        $orStatementUser->add(\sprintf('%s.id = :user', $misMembersAlias));

        $andX->add($orStatementUser);
        $orStatements->add($andX);

        $queryBuilder->andWhere($orStatements);

        $queryBuilder->setParameter('false', false);
        $queryBuilder->setParameter('true', true);
        $queryBuilder->setParameter('user', $user);
    }
}

<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\Task\Task;
use App\Security\Provider\Confidential\Task\ConfidentialSecurityProviderInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class TaskExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        #[AutowireIterator(tag: 'task.security.provider')]
        private readonly iterable $providers = [],
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (!$operation instanceof GetCollection) {
            return;
        }

        $this->apply($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        if (!$operation instanceof Get) {
            return;
        }

        $this->apply($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    private function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): void
    {
        if (Task::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $orStatement = $queryBuilder->expr()->orX();
        $andX = $queryBuilder->expr()->andX();

        $assigneeOrAssignorStatement = $queryBuilder->expr()->orX();
        $assigneeOrAssignorStatement
            ->add(\sprintf('%s.assignee = :user', $rootAlias))
            ->add(\sprintf('%s.createdBy = :user', $rootAlias))
        ;

        $andX
            ->add(\sprintf('%s.confidential = :true', $rootAlias))
            ->add($assigneeOrAssignorStatement)
        ;

        $orStatement
            ->add(\sprintf('%s.confidential = :false', $rootAlias))
            ->add($andX)
        ;

        $moduleAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'module', Join::LEFT_JOIN);
        /** @var ConfidentialSecurityProviderInterface $provider */
        foreach ($this->providers as $provider) {
            $orStatement->add($provider->provideOrStatement($queryBuilder, $queryNameGenerator, $moduleAlias));
        }

        $queryBuilder->andWhere($orStatement);
        $queryBuilder->setParameter('user', $this->security->getUser());
        $queryBuilder->setParameter('true', true);
        $queryBuilder->setParameter('false', false);
    }
}

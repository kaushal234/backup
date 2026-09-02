<?php

declare(strict_types=1);

namespace App\Security\Provider\Confidential\Task\MIS\Project;

use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\MIS\Project\Project;
use App\Entity\Task\Task;
use App\Security\Provider\Confidential\Task\ConfidentialSecurityProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectConfidentialSecurityProvider implements ConfidentialSecurityProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function isGranted(object $entity): bool
    {
        if (!$entity instanceof Task || null === $entity->module || 'MIS' !== $entity->module->getName()) {
            return false;
        }

        $project = $this->entityManager
            ->getRepository(Project::class)
            ->find($entity->referenceId);

        if (!$project instanceof Project) {
            return false;
        }

        return $this->isProjectGranted($project);
    }

    public function isProjectGranted(Project $project): bool
    {
        if (!$project->isConfidential()) {
            return true;
        }

        if ($this->security->isGranted('FEATURE_MIS_PROJECT_READ_CONFIDENTIAL')) {
            return true;
        }

        $user = $this->security->getUser();

        return $project->projectManager === $user
            || $project->misOwner === $user
            || $project->getModuleKeyUsers()->contains($user)
            || $project->getMisMembers()->contains($user);
    }

    public function provideOrStatement(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $moduleAlias): ?Orx
    {
        $queryBuilder->setParameter('module_name', 'MIS');

        if ($this->security->isGranted('FEATURE_MIS_PROJECT_READ_CONFIDENTIAL')) {
            return $queryBuilder->expr()->orX()->add(\sprintf('%s.name = :module_name', $moduleAlias));
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $projectAlias = 'project';
        $queryBuilder->leftJoin(Project::class, $projectAlias, Join::WITH, \sprintf('%s.referenceId = project.id', $rootAlias));

        $moduleKeyUsersAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $projectAlias, 'moduleKeyUsers', Join::LEFT_JOIN);
        $misMembersAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $projectAlias, 'misMembers', Join::LEFT_JOIN);

        $orStatementUser = $queryBuilder->expr()->orX();
        $orStatementUser->add(\sprintf('%s.projectManager = :user', $projectAlias));
        $orStatementUser->add(\sprintf('%s.misOwner = :user', $projectAlias));
        $orStatementUser->add(\sprintf('%s.id = :user', $moduleKeyUsersAlias));
        $orStatementUser->add(\sprintf('%s.id = :user', $misMembersAlias));

        $andX = $queryBuilder->expr()->andX();
        $andX
            ->add(\sprintf('%s.confidential = :true', $projectAlias))
            ->add(\sprintf('%s.name = :module_name', $moduleAlias))
            ->add($orStatementUser);

        $orStatement = $queryBuilder->expr()->orX();
        $orStatement->add($andX);

        return $orStatement;
    }
}

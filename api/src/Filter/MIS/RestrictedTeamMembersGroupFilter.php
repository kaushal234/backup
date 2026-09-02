<?php

declare(strict_types=1);

namespace App\Filter\MIS;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\Group;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RestrictedTeamMembersGroupFilter implements FilterInterface
{
    final public const PROPERTY = 'restricted';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security,
    ) {
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (Group::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Group resource');
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(static::PROPERTY) || !$request->query->get(static::PROPERTY)) {
            return;
        }

        /** @var People $user */
        $user = $this->security->getUser();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $aclAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'acls');
        $peopleAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $aclAlias, 'user');
        $teamMemberAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $peopleAlias, 'supervisor');
        $subAclAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $teamMemberAlias, 'acls');

        $orStatements = $queryBuilder->expr()->orX();

        $orStatements->add($queryBuilder->expr()->eq(\sprintf('%s.user', $aclAlias), ':user'));
        $orStatements->add($queryBuilder->expr()->eq(\sprintf('%s.user', $subAclAlias), ':user'));

        $queryBuilder->andWhere($orStatements);

        $queryBuilder->setParameter('user', $user)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::PROPERTY => [
                'property' => static::PROPERTY,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}

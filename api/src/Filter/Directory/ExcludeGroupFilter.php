<?php

declare(strict_types=1);

namespace App\Filter\Directory;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ExcludeGroupFilter implements FilterInterface
{
    /**
     * @var string
     */
    private const FILTER_EXCLUDE_NAME = 'excludeGroup';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (People::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the People resource');
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(self::FILTER_EXCLUDE_NAME)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $subQueryBuilder = $this->entityManager->createQueryBuilder();
        $subQueryBuilder
            ->select($subQueryBuilder->expr()->count('acl.id'))
            ->from(Acl::class, 'acl')
            ->leftJoin(Group::class, 'g', Join::WITH, 'g = acl.group')
            ->where($subQueryBuilder->expr()->eq('g.name', ':group'))
            ->andWhere($subQueryBuilder->expr()->eq(\sprintf('%s.id', $rootAlias), 'acl.user'))
        ;

        $queryBuilder
            ->andWhere(\sprintf('(%s) = 0', $subQueryBuilder->getDQL()))
            ->setParameter('group', $request->query->get(self::FILTER_EXCLUDE_NAME))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            self::FILTER_EXCLUDE_NAME => [
                'property' => self::FILTER_EXCLUDE_NAME,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}

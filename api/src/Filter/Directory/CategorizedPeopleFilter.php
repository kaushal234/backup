<?php

declare(strict_types=1);

namespace App\Filter\Directory;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CategorizedPeopleFilter implements FilterInterface
{
    final public const PROPERTY = 'categorized';

    protected RequestStack $requestStack;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (People::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the People resource');
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(static::PROPERTY)) {
            return;
        }

        $value = \in_array($request->query->get(static::PROPERTY), [false, 'false', '0'], true);

        $subQuery = $this->entityManager->createQueryBuilder();
        $subQuery
            ->select('sub_p.id')
            ->from(People::class, 'sub_p')
            ->join('sub_p.businessUnit', 'sub_bu')
            ->join('sub_bu.positionClassifications', 'sub_pc')
            ->join('sub_pc.positions', 'sub_pos')
            ->andWhere('sub_p.position = sub_pos.id')
            ->andWhere('sub_p.disabled = :sub_disabled')
        ;

        $queryBuilder
            ->andWhere(\sprintf('o.id %s IN (%s)', $value ? 'NOT' : '', $subQuery->getDQL()))
            ->GroupBy('o.id')
            ->setParameter('sub_disabled', false)
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

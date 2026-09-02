<?php

declare(strict_types=1);

namespace App\Report;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ReportQueriesBuilder
{
    /**
     * @var string
     */
    final public const UNDEFINED_LABEL = "'undefined'";

    /**
     * @var string
     */
    final public const SELECT_PART_VALUE = 'COUNT(o) AS value';

    private QueryBuilder $mainQueryBuilder;

    /**
     * @var QueryBuilder[]
     */
    private array $extraQueryBuilders = [];

    private readonly EntityRepository $entityRepository;

    public function __construct(ManagerRegistry $registry, EntityRepository $entityRepository, string $x, string $y)
    {
        $this->entityRepository = $entityRepository;

        $qb = $this->entityRepository->createQueryBuilder('o');

        $qb->select(self::SELECT_PART_VALUE);

        foreach (['x' => explode('.', $x), 'y' => explode('.', $y)] as $key => $elements) {
            if (1 === \count($elements)) {
                $property = $elements[0];
                $qb->addSelect(\sprintf('COALESCE(o.%s, %s) AS %s', $property, self::UNDEFINED_LABEL, $key));
                $qb->addGroupBy(\sprintf('o.%s', $property));
                $qb->addOrderBy(\sprintf('o.%s', $property));

                $extraQb = $this->entityRepository->createQueryBuilder('o');
                $extraQb->select(\sprintf('o.%s AS %s', $property, $key))->distinct()->orderBy(\sprintf('o.%s', $property));
                $this->extraQueryBuilders[$key] = $extraQb;
            } else {
                $targetClass = null;
                foreach ($elements as $n => $segment) {
                    if (0 === $n || null !== $targetClass) {
                        $metadata = $registry->getManager()->getMetadataFactory()->getMetadataFor($targetClass ?? $this->entityRepository->getClassName());
                        $associationNames = $metadata->getAssociationNames();
                        if (\in_array($segment, $associationNames, true)) {
                            $targetClass = $metadata->getAssociationTargetClass($segment);
                        }
                    }

                    $previousAlias = 0 === $n ? 'o' : \sprintf('%s_%d', $key, $n - 1);
                    if ($n + 1 !== \count($elements)) {
                        $qb->join(\sprintf('%s.%s', $previousAlias, $segment), \sprintf('%s_%d', $key, $n));
                        continue;
                    }

                    /** @var EntityRepository $targetClassEntityRepository */
                    $targetClassEntityRepository = $registry->getManager()->getRepository($targetClass);
                    $extraQb = $targetClassEntityRepository->createQueryBuilder('o');
                    $extraQb->select(\sprintf('o.%s AS %s', $segment, $key))->distinct()->orderBy(\sprintf('o.%s', $segment));
                    $this->extraQueryBuilders[$key] = $extraQb;

                    $qb->addSelect(\sprintf('COALESCE(%s.%s, %s) AS %s', $previousAlias, $segment, self::UNDEFINED_LABEL, $key));
                    $qb->addGroupBy(\sprintf('%s.%s', $previousAlias, $segment));
                    $qb->addOrderBy(\sprintf('%s.%s', $previousAlias, $segment));
                }
            }
        }

        $this->mainQueryBuilder = $qb;
    }

    public static function replaceSelectValuePart(QueryBuilder $queryBuilder, string $replacement)
    {
        $selectParts = $queryBuilder->getDQLPart('select');
        $queryBuilder->resetDQLPart('select');
        /** @var Select $selectPart */
        foreach ($selectParts as $selectPart) {
            if ($selectPart->getParts() === [self::SELECT_PART_VALUE]) {
                $queryBuilder->addSelect($replacement);

                continue;
            }
            $queryBuilder->addSelect($selectPart->getParts());
        }
    }

    public function getMainQueryBuilder(): QueryBuilder
    {
        return $this->mainQueryBuilder;
    }

    public function setMainQueryBuilder(QueryBuilder $mainQueryBuilder)
    {
        $this->mainQueryBuilder = $mainQueryBuilder;
    }

    /**
     * @return QueryBuilder[]
     */
    public function getExtraQueryBuilders(): array
    {
        return $this->extraQueryBuilders;
    }

    public function getXQueryBuilder(): QueryBuilder
    {
        return $this->extraQueryBuilders['x'];
    }

    public function setXQueryBuilder(QueryBuilder $qb): self
    {
        $this->extraQueryBuilders['x'] = $qb;

        return $this;
    }

    public function getYQueryBuilder(): QueryBuilder
    {
        return $this->extraQueryBuilders['y'];
    }

    public function setYQueryBuilder(QueryBuilder $qb): self
    {
        $this->extraQueryBuilders['y'] = $qb;

        return $this;
    }

    public function getNewQueryBuilder(): QueryBuilder
    {
        return $this->entityRepository->createQueryBuilder('o');
    }
}

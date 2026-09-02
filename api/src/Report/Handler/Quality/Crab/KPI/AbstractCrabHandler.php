<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\Crab\KPI;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Quality\Crab;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractCrabHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    protected QueryBuilder $queryBuilder;
    private readonly IriConverterInterface $iriConverter;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter)
    {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
        $this->queryBuilder = $this->entityManager->createQueryBuilder();
    }

    public function buildDefaultRequest(string $x, string $y, array $options = []): bool
    {
        if (!isset($options['factory']) && !isset($options['category'])) {
            return false;
        }

        $this->queryBuilder
            ->select('COUNT(crab) AS value')
            ->addSelect('l.name AS y')
            ->from(Crab::class, 'crab')
            ->leftJoin(EquipmentRecord::class, 'er', Join::WITH, 'crab.equipmentRecord = er')
            ->leftJoin(Location::class, 'l', Join::WITH, 'er.manufacturerLocation = l')
            ->where('er.product IS NOT NULL')
            ->andWhere('crab.createdAt > :one_year_ago')
            ->orderBy('value', Criteria::DESC)
            ->setParameter('one_year_ago', (new \DateTime('1 year ago'))->format('Y-m-d'));

        if (($options['factory'] ?? null) !== null) {
            $factory = $this->iriConverter->getResourceFromIri($options['factory']);
            if (!$factory instanceof Location) {
                return false;
            }
            $this->queryBuilder
                ->andWhere('l = :factory')
                ->setParameter('factory', $factory);
        }

        if (($options['category'] ?? null) !== null) {
            $this->queryBuilder
                ->andWhere('crab.category = :category')
                ->setParameter('category', $options['category']);
        }

        return true;
    }
}

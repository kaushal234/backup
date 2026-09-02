<?php

declare(strict_types=1);

namespace App\Repository\Support;

use App\Entity\EquipmentRecord;
use App\Entity\Support\EquipmentSerial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EquipmentSerial>
 */
class EquipmentSerialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipmentSerial::class);
    }

    public function getSchematicsForEquipmentRecord(EquipmentRecord $equipmentRecord): array
    {
        $qb = $this->createQueryBuilder('es');

        $qb
            ->select('es.serial')
            ->leftJoin('es.component', 'c')
            ->where('c.name LIKE :schem')
            ->orwhere('c.name LIKE :diag')
            ->orwhere('c.name = :menu')
            ->andWhere('es.equipmentRecord = :equipmentRecord')
            ->setParameter('equipmentRecord', $equipmentRecord)
            ->setParameter('schem', '%schem%')
            ->setParameter('diag', '%diag%')
            ->setParameter('menu', 'menu tree')
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function findByComponentNameAndEquipmentRecord(EquipmentRecord $equipmentRecord, string $componentName)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $queryBuilder
            ->select('s.model as model, s.serial as serial, s.brand as brand, s.id as id, c.id as componentId, c.name as componentName')
            ->join('s.component', 'c')
            ->where('s.equipmentRecord = :equipmentRecord')
            ->andWhere('c.name = :componentName')
            ->setParameter('equipmentRecord', $equipmentRecord)
            ->setParameter('componentName', $componentName)
        ;

        return $queryBuilder->getQuery()->getScalarResult();
    }

    public function createOrFindExistingSerial(EquipmentSerial $equipmentSerial): EquipmentSerial
    {
        $existingSerial = $this->findOneBy([
            'equipmentRecord' => $equipmentSerial->equipmentRecord,
            'component' => $equipmentSerial->component,
            'model' => $equipmentSerial->model,
            'serial' => $equipmentSerial->serial,
            'brand' => $equipmentSerial->brand,
        ]);

        if (null !== $existingSerial) {
            return $existingSerial;
        }

        $this->getEntityManager()->persist($equipmentSerial);

        return $equipmentSerial;
    }

    /**
     * Custom repository method used by the UniqueEntity constraint on EquipmentSerial.
     *
     * Excludes serials that have been removed from the EquipmentRecord's in-memory
     * serials collection (they will be deleted on flush via orphanRemoval). This allows
     * a user to drop one of two pre-existing duplicates through a PUT request without
     * the remaining twin being reported as non-unique during validation, which runs
     * before the flush that would actually delete the removed serial.
     *
     * @param array<string, mixed> $criteria
     *
     * @return EquipmentSerial[]
     */
    public function findBySkippingRemovedFromCollection(array $criteria): array
    {
        $results = $this->findBy($criteria);

        $equipmentRecord = $criteria['equipmentRecord'] ?? null;
        if (!$equipmentRecord instanceof EquipmentRecord) {
            return $results;
        }

        return array_values(array_filter(
            $results,
            static fn (EquipmentSerial $serial) => $equipmentRecord->getSerials()->contains($serial)
        ));
    }
}

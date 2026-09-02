<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Entity\Service\CustomerServiceRecord\ServiceBulletinCustomerServiceRecord;
use Doctrine\DBAL\Connection;

class CustomerServiceRecordManager
{
    private readonly Connection $legacyConnection;

    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function getCustomerServiceRecordByEquipmentRecord(int $legacyId): array
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $queryBuilder
            ->select('csr.*')
            ->from('csr', 'csr')
            ->where('parent_id = :erId')
            ->andWhere('status IN ("IN PROGRESS", "PENDING")')
            ->andWhere('work_type = "Commissioning"')
            ->setParameter('erId', $legacyId)
        ;

        $stmt = $this->legacyConnection->prepare($queryBuilder->getSQL());
        foreach ($queryBuilder->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function updateDateSchedule(int $legacyId, string $dateSchedule): void
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $queryBuilder
            ->update('csr')
            ->set('dt_sche', ':dt_sche')
            ->where('id = :id')
            ->setParameters([
                'id' => $legacyId,
                'dt_sche' => $dateSchedule,
            ])
        ;

        $stmt = $this->legacyConnection->prepare($queryBuilder->getSQL());
        foreach ($queryBuilder->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->executeQuery();
    }

    public function doubleWriteFromIntervention(Intervention $intervention)
    {
        // Maybe set default value on legacy csr table for send tech : ALTER TABLE csr ALTER COLUMN send_tech SET DEFAULT 'N';
        $sql = \sprintf(
            'UPDATE csr
                SET
                    tech_id = %d,
                    send_tech = \'Y\',
                    dt_work = \'%s\',
                    dt_completed = \'%s\',
                    module = \'%s\',
                    module_id = %d
                WHERE id = %d',
            $intervention->leader->getLegacyId(),
            $intervention->startedAt ? $intervention->startedAt->format('Y-m-d H:i:s') : '0000-00-00 00:00:00',
            $intervention->endedAt ? $intervention->endedAt->format('Y-m-d H:i:s') : '0000-00-00 00:00:00',
            $intervention->customerServiceRecord->getLegacyModuleName(),
            $intervention->customerServiceRecord->getLegacyModuleId(),
            $intervention->customerServiceRecord->getLegacyId(),
        );

        $this->legacyConnection->prepare($sql)->executeStatement();
    }

    public function findServiceBulletinLine(ServiceBulletinCustomerServiceRecord $customerServiceRecord)
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $queryBuilder
            ->select('sbl.*')
            ->from('sb_lines', 'sbl')
            ->where('sbl.parent_id = :sbId')
            ->andWhere('sbl.er_id = :erId')
            ->setParameter('sbId', $customerServiceRecord->serviceBulletinLegacyId)
            ->setParameter('erId', $customerServiceRecord->equipmentRecord->getLegacyId())
        ;

        $stmt = $this->legacyConnection->prepare($queryBuilder->getSQL());
        foreach ($queryBuilder->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->executeQuery()->fetchAssociative();
    }
}

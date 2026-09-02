<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use App\Entity\Service\CustomerServiceRecord\ServiceBulletinCustomerServiceRecord;
use Doctrine\DBAL\Connection;

class ServiceBulletinLineManager
{
    public function __construct(
        private readonly Connection $legacyConnection,
    ) {
    }

    public function addCustomerServiceRecord(ServiceBulletinCustomerServiceRecord $customerServiceRecord): void
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $queryBuilder
            ->update('sb_lines')
            ->set('csr_id', ':csrId')
            ->where('id = :id')
            ->setParameters([
                'id' => $customerServiceRecord->serviceBulletinLinesLegacyId,
                'csrId' => $customerServiceRecord->getLegacyId(),
            ])
        ;

        $stmt = $this->legacyConnection->prepare($queryBuilder->getSQL());
        foreach ($queryBuilder->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->executeQuery();
    }

    public function changeStatus(int $serviceBulletinLineId, string $status): void
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $queryBuilder
            ->update('sb_lines')
            ->set('status', ':status')
            ->where('id = :id')
            ->setParameters([
                'id' => $serviceBulletinLineId,
                'status' => $status,
            ])
        ;

        $stmt = $this->legacyConnection->prepare($queryBuilder->getSQL());
        foreach ($queryBuilder->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->executeQuery();
    }
}

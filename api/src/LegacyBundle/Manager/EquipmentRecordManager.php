<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class EquipmentRecordManager
{
    private readonly Connection $legacyConnection;

    private readonly EntityManagerInterface $em;

    private readonly IriConverterInterface $iriConverter;

    public function __construct(Connection $legacyConnection, EntityManagerInterface $em, IriConverterInterface $iriConverter)
    {
        $this->legacyConnection = $legacyConnection;
        $this->em = $em;
        $this->iriConverter = $iriConverter;
    }

    public function transferCustomer(Customer $sourceCustomer, Customer $targetCustomer)
    {
        $equipmentRecords = [];
        foreach (['buyer_customer_id', 'customer_id'] as $type) {
            $equipmentRecords = [...$equipmentRecords, ...$this->getEquipmentRecordsForCustomer($sourceCustomer, $type)];
            if ([] === $equipmentRecords) {
                continue;
            }
            $sql = \sprintf('UPDATE service SET %s = :target_customer_id WHERE %s = :source_customer_id', $type, $type);
            $this->legacyConnection->executeStatement($sql, ['target_customer_id' => $targetCustomer->getLegacyId(), 'source_customer_id' => $sourceCustomer->getLegacyId()]);
        }

        $message = [] === $equipmentRecords ? 'No equipment record updated.' : \sprintf('Equipment Records #%s updated.', implode(', #', array_column($equipmentRecords, 'sn')));
        $transferApiLog =
            (new Comment())
                ->setMessage($message)
                ->setResource($this->iriConverter->getIriFromResource($sourceCustomer));

        $this->em->persist($transferApiLog);
    }

    public function findByLegacyId(int $legacyId): array
    {
        $result = $this->legacyConnection->fetchAssociative(
            'SELECT id,
                        IF(dgt_act = "0000-00-00 00:00:00", NULL, dgt_act) as dgt_act,
                        IF(dyt = "0000-00-00 00:00:00", NULL, dyt) as dyt,
                        esrid
                        FROM service WHERE id = :legacyId', ['legacyId' => $legacyId]);

        return \is_array($result) ? $result : [];
    }

    public function isSolLinked(int $legacyId): bool
    {
        $result = $this->legacyConnection->fetchAssociative(
            'SELECT sor_lines.id as sol_id
                    FROM service
                    LEFT JOIN sor_units ON sor_units.id = service.sor_uid
                    LEFT JOIN sor_lines ON sor_lines.id = sor_units.parent_id
                    WHERE service.id = :legacyId', ['legacyId' => $legacyId]);

        return !empty($result['solid']);
    }

    public function unsetEsrIdProperty(EquipmentShippingRecord $deletedEquipmentShippingRecord, EquipmentRecord $equipmentRecord): void
    {
        // get er on TLD
        $erOnLegacy = $this->findByLegacyId($equipmentRecord->getLegacyId());
        if (!$erOnLegacy) {
            return;
        }

        if ($erOnLegacy['esrid'] === $deletedEquipmentShippingRecord->getLegacyId()) {
            $unsetQueryBuilder = $this->legacyConnection->createQueryBuilder();
            $unsetQueryBuilder->update('service');
            $unsetQueryBuilder->set('esrid', 'NULL');
            $unsetQueryBuilder->where('id = :er_id');
            $unsetQueryBuilder->setParameters(['er_id' => $erOnLegacy['id']]);

            $stmt = $this->legacyConnection->prepare($unsetQueryBuilder->getSQL());
            foreach ($unsetQueryBuilder->getParameters() as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->executeStatement();
        }
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function updateEsrIdProperty(EquipmentShippingRecord $updatedEquipmentShippingRecord, EquipmentRecord $equipmentRecord)
    {
        // get er on TLD
        $erOnLegacy = $this->findByLegacyId($equipmentRecord->getLegacyId());
        if (!$erOnLegacy) {
            return;
        }
        if ($erOnLegacy['esrid'] !== $updatedEquipmentShippingRecord->getLegacyId()) {
            $updateQueryBuilder = $this->legacyConnection->createQueryBuilder();
            $updateQueryBuilder->update('service');
            $updateQueryBuilder->set('esrId', (string) $updatedEquipmentShippingRecord->getLegacyId() ?: 0);
            $updateQueryBuilder->where('id = :er_id');
            $updateQueryBuilder->setParameters(['er_id' => $erOnLegacy['id']]);

            $stmt = $this->legacyConnection->prepare($updateQueryBuilder->getSQL());
            foreach ($updateQueryBuilder->getParameters() as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->executeStatement();
        }
    }

    private function getEquipmentRecordsForCustomer(Customer $customer, string $type): array
    {
        return $this->legacyConnection->fetchAllAssociative(\sprintf('SELECT * FROM service WHERE %s = :customer', $type), ['customer' => $customer->getLegacyId()]);
    }
}

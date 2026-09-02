<?php

declare(strict_types=1);

namespace LegacyBundle\CollectionHandler;

use App\Entity\EquipmentRecord;
use App\Entity\Support\EquipmentSerial;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\PersistentCollection;
use LegacyBundle\Entity\LegacyIdInterface;

class SerialCollectionHandler implements CollectionHandlerInterface
{
    private readonly Connection $legacyConnection;

    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function handleUpdates(PersistentCollection $collection): void
    {
        /** @var EquipmentRecord $equipmentRecord */
        $equipmentRecord = $collection->getOwner();

        if (!$equipmentRecord instanceof LegacyIdInterface) {
            throw new \InvalidArgumentException('The owning class must implement LegacyIdInterface');
        }

        /** @var EquipmentSerial $serial */
        foreach ($equipmentRecord->getSerials() as $serial) {
            $this->legacyConnection->beginTransaction();
            try {
                $sql = 'UPDATE service_serials
                        SET
                            parent_id= :parent_id,
                            component= :component,
                            model= :model,
                            serial = :serial,
                            brand = :brand,
                            manid = 0
                        WHERE id = :id';
                $stmt = $this->legacyConnection->prepare($sql);
                $stmt->bindValue('parent_id', $equipmentRecord->getLegacyId());
                $stmt->bindValue('component', $serial->component->name);
                $stmt->bindValue('model', $serial->model);
                $stmt->bindValue('serial', $serial->serial);
                $stmt->bindValue('brand', $serial->brand);
                $stmt->bindValue('id', $serial->getLegacyId());
                $stmt->executeStatement();
                $this->legacyConnection->commit();
            } catch (\Exception $exception) {
                $this->legacyConnection->rollBack();
                throw $exception;
            }
        }
    }

    public function handleDeletions(PersistentCollection $collection): void
    {
    }

    public function supports(PersistentCollection $collection, ?string $targetEntity): bool
    {
        return $collection->getOwner() instanceof EquipmentRecord && EquipmentSerial::class === $targetEntity;
    }
}

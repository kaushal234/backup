<?php

declare(strict_types=1);

namespace LegacyBundle\CollectionHandler;

use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\PersistentCollection;

class CustomerTypeCollectionHandler implements CollectionHandlerInterface
{
    private readonly Connection $legacyConnection;

    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function handleUpdates(PersistentCollection $collection)
    {
        /** @var Customer $owner */
        $owner = $collection->getOwner();

        $this->legacyConnection->beginTransaction();
        if ($collection->isEmpty()) {
            try {
                $sql = 'UPDATE customers SET type= "" WHERE id = :id';
                $stmt = $this->legacyConnection->prepare($sql);
                $stmt->bindValue('id', $owner->getLegacyId());
                $stmt->executeStatement();
                $this->legacyConnection->commit();
            } catch (\Exception $exception) {
                $this->legacyConnection->rollBack();
                throw $exception;
            }

            return;
        }

        $array = [];
        /** @var CustomerType $object */
        foreach ($collection as $object) {
            $array[] = $object->getName();
        }

        try {
            $sql = 'UPDATE customers SET type= :type WHERE id = :id';
            $stmt = $this->legacyConnection->prepare($sql);
            $stmt->bindValue('type', implode(', ', $array));
            $stmt->bindValue('id', $owner->getLegacyId());
            $stmt->executeStatement();
            $this->legacyConnection->commit();
        } catch (\Exception $exception) {
            $this->legacyConnection->rollBack();
            throw $exception;
        }
    }

    public function handleDeletions(PersistentCollection $collection)
    {
    }

    public function supports(PersistentCollection $collection, ?string $targetEntity): bool
    {
        return $collection->getOwner() instanceof Customer && CustomerType::class === $targetEntity;
    }
}

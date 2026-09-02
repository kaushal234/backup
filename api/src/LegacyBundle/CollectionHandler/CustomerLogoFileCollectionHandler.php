<?php

declare(strict_types=1);

namespace LegacyBundle\CollectionHandler;

use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerLogoFile;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\PersistentCollection;

class CustomerLogoFileCollectionHandler implements CollectionHandlerInterface
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

        $logo = $collection->first();

        if (!$logo instanceof CustomerLogoFile) {
            return;
        }

        $this->legacyConnection->beginTransaction();

        try {
            $sql = 'UPDATE customers SET logo_file = :filePath WHERE id = :id';
            $stmt = $this->legacyConnection->prepare($sql);
            $stmt->bindValue('filePath', $logo->getFilePath());
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
        return $collection->getOwner() instanceof Customer && CustomerLogoFile::class === $targetEntity;
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\CollectionHandler;

use App\Entity\Support\ManualDocument;
use App\Entity\Support\ManualDocumentFile;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\PersistentCollection;

class ManualDocumentFileCollectionHandler implements CollectionHandlerInterface
{
    private readonly Connection $legacyConnection;

    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function handleUpdates(PersistentCollection $collection): void
    {
        /** @var ManualDocument $owner */
        $owner = $collection->getOwner();

        $file = $collection->first();

        if (!$file instanceof ManualDocumentFile) {
            return;
        }

        $this->updateManualDocumentFile($file->getFilePath(), $owner->getLegacyId());
    }

    public function handleDeletions(PersistentCollection $collection): void
    {
        /** @var ManualDocument $owner */
        $owner = $collection->getOwner();

        $this->updateManualDocumentFile('', $owner->getLegacyId());
    }

    public function supports(PersistentCollection $collection, ?string $targetEntity): bool
    {
        return $collection->getOwner() instanceof ManualDocument && ManualDocumentFile::class === $targetEntity;
    }

    public function updateManualDocumentFile(string $filepath, int $legacyId): void
    {
        $this->legacyConnection->beginTransaction();

        try {
            $sql = 'UPDATE manuals_diag SET diagram_filename= :filePath WHERE id = :id';
            $stmt = $this->legacyConnection->prepare($sql);
            $stmt->bindValue('filePath', $filepath);
            $stmt->bindValue('id', $legacyId);
            $stmt->executeStatement();
            $this->legacyConnection->commit();
        } catch (\Exception $exception) {
            $this->legacyConnection->rollBack();
            throw $exception;
        }
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\CollectionHandler;

use App\Entity\Directory\People;
use App\Entity\Directory\PeopleFile;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\PersistentCollection;

class PeopleFileCollectionHandler implements CollectionHandlerInterface
{
    private readonly Connection $legacyConnection;

    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function handleUpdates(PersistentCollection $collection)
    {
        /** @var People $owner */
        $owner = $collection->getOwner();

        $photo = $collection->first();

        if (!$photo instanceof PeopleFile) {
            return;
        }

        $this->updatePeoplePhoto($photo->getFilePath(), $owner->getLegacyId());
    }

    public function handleDeletions(PersistentCollection $collection)
    {
        /** @var People $owner */
        $owner = $collection->getOwner();

        $this->updatePeoplePhoto('', $owner->getLegacyId());
    }

    public function supports(PersistentCollection $collection, ?string $targetEntity): bool
    {
        return $collection->getOwner() instanceof People && PeopleFile::class === $targetEntity;
    }

    public function updatePeoplePhoto(string $filepath, int $legacyId)
    {
        $this->legacyConnection->beginTransaction();

        try {
            $sql = 'UPDATE people SET photo= :filePath WHERE id = :id';
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

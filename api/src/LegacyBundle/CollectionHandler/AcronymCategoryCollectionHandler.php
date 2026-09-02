<?php

declare(strict_types=1);

namespace LegacyBundle\CollectionHandler;

use App\Entity\Acronym;
use App\Entity\AcronymCategory;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\PersistentCollection;

class AcronymCategoryCollectionHandler implements CollectionHandlerInterface
{
    private readonly Connection $legacyConnection;

    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function handleUpdates(PersistentCollection $collection)
    {
        /** @var Acronym $owner */
        $owner = $collection->getOwner();
        $id = $owner->getLegacyId();
        $this->legacyConnection->beginTransaction();

        try {
            $this->doDelete($id);

            $sql = 'INSERT INTO agrl (parent_id, type) VALUES (:parent_id, :type)';
            $stmt = $this->legacyConnection->prepare($sql);

            foreach ($collection as $category) {
                $stmt->bindValue('parent_id', $id);
                $stmt->bindValue('type', $category->getName() ?? null);
                $stmt->executeQuery();
            }
            $this->legacyConnection->commit();
        } catch (\Exception $exception) {
            $this->legacyConnection->rollBack();
            throw $exception;
        }
    }

    public function handleDeletions(PersistentCollection $collection)
    {
        $this->legacyConnection->beginTransaction();
        /** @var Acronym $owner */
        $owner = $collection->getOwner();
        try {
            $this->doDelete($owner->getLegacyId());
            $this->legacyConnection->commit();
        } catch (\Exception $exception) {
            $this->legacyConnection->rollBack();
            throw $exception;
        }
    }

    public function supports(PersistentCollection $collection, ?string $targetEntity): bool
    {
        return $collection->getOwner() instanceof Acronym && AcronymCategory::class === $targetEntity;
    }

    private function doDelete($id)
    {
        $sql = 'DELETE FROM agrl WHERE parent_id = :id';
        $stmt = $this->legacyConnection->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->executeStatement();
    }
}

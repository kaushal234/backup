<?php

declare(strict_types=1);

namespace LegacyBundle\CollectionHandler;

use App\ApiPlatform\UniqueResourceMetadataCollectionFactory;
use App\Entity\Directory\Phone;
use App\Entity\PhoneInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\PersistentCollection;
use LegacyBundle\Doctrine\Mapping\Attributes\Synchronize;
use LegacyBundle\Entity\LegacyIdInterface;

class PhoneCollectionHandler implements CollectionHandlerInterface
{
    /**
     * @var string
     */
    final public const ATTRIBUTE_KEY = '_user_phones_field_map';

    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly UniqueResourceMetadataCollectionFactory $resourceMetadataFactory)
    {
    }

    public function handleUpdates(PersistentCollection $collection)
    {
        $owner = $collection->getOwner();

        if (!$owner instanceof PhoneInterface || !$owner instanceof LegacyIdInterface) {
            throw new \InvalidArgumentException('the owning class must implement both PhoneInterface & LegacyIdInterface');
        }
        $realClass = ClassUtils::getRealClass($class = $owner::class);
        $reflection = new \ReflectionClass($realClass);
        $attribute = $reflection->getAttributes(Synchronize::class)[0];
        /** @var Synchronize $synchronize */
        $synchronize = $attribute->newInstance();

        $ownerId = $owner->getLegacyId();

        $extraProperties = $this->resourceMetadataFactory->getExtraProperties($realClass);
        $fieldsMap = $extraProperties[self::ATTRIBUTE_KEY];

        $phones = array_fill_keys(array_values($fieldsMap), '');
        foreach ($owner->getPhones() as $phone) {
            if (!isset($fieldsMap[$phone->getType()])) {
                // This kind of phone does not exists in legacy application
                continue;
            }

            $phones[$fieldsMap[$phone->getType()]] = $phone->getNumber();
        }

        $this->legacyConnection->beginTransaction();
        try {
            $this->updatePhones($ownerId, $phones, $synchronize->table);
            $this->legacyConnection->commit();
        } catch (\Exception $exception) {
            $this->legacyConnection->rollBack();
            throw $exception;
        }
    }

    public function handleDeletions(PersistentCollection $collection)
    {
        /** @var LegacyIdInterface $owner */
        $owner = $collection->getOwner();

        $reflection = new \ReflectionClass($owner);
        $attribute = $reflection->getAttributes(Synchronize::class)[0];
        /** @var Synchronize $synchronize */
        $synchronize = $attribute->newInstance();

        $this->legacyConnection->beginTransaction();

        $extraProperties = $this->resourceMetadataFactory->getExtraProperties($owner::class);
        $fieldsMap = $extraProperties[self::ATTRIBUTE_KEY];
        $emptyFields = array_fill_keys(array_values($fieldsMap), '');
        try {
            $this->updatePhones($owner->getLegacyId(), $emptyFields, $synchronize->table);
            $this->legacyConnection->commit();
        } catch (\Exception $exception) {
            $this->legacyConnection->rollBack();
            throw $exception;
        }
    }

    public function supports(PersistentCollection $collection, ?string $targetEntity): bool
    {
        return $collection->getOwner() instanceof PhoneInterface && Phone::class === $targetEntity;
    }

    private function updatePhones($ownerId, array $fields, string $table)
    {
        if ([] === $fields) {
            return;
        }

        $sql = \sprintf(
            'UPDATE %s SET %s WHERE id= :id',
            $table,
            implode(', ', array_map(static fn ($field) => $field.' = :'.$field, array_keys($fields)))
        );

        $stmt = $this->legacyConnection->prepare($sql);
        foreach ($fields as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('id', $ownerId);
        $stmt->executeStatement();
    }
}

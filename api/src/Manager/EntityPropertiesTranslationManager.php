<?php

declare(strict_types=1);

namespace App\Manager;

use App\Http\DeeplClient;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

readonly class EntityPropertiesTranslationManager
{
    public function __construct(
        private DeeplClient $deeplClient,
        private PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    public function translateObjectProperties(object $object, array $propertiesMapping): object
    {
        foreach ($propertiesMapping as $original => $translated) {
            $originalValue = $this->propertyAccessor->getValue($object, $original);

            if (null === $originalValue) {
                $this->propertyAccessor->setValue($object, $translated, null);
                continue;
            }

            $this->propertyAccessor->setValue(
                $object,
                $translated,
                $this->deeplClient->getTranslation(
                    baseText: $originalValue,
                    class: $object::class,
                    itemId: $object->getId(),
                    returnOnlyTranslatedMessage: true,
                    createLog: false,
                ),
            );
        }

        return $object;
    }
}

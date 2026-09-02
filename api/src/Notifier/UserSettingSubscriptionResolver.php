<?php

declare(strict_types=1);

namespace App\Notifier;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\User;
use App\Entity\UserSetting;
use App\Repository\UserSettingRepository;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class UserSettingSubscriptionResolver
{
    public function __construct(
        private readonly UserSettingRepository $userSettingRepository,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    /**
     * Returns users whose UserSetting filters all match the given entity.
     *
     * @param string[] $scalarFields Fields whose values are plain scalars (not IRIs)
     *
     * @return array<User>
     */
    public function findSubscribers(string $settingKey, object $entity, array $scalarFields = []): array
    {
        $subscribers = [];
        foreach ($this->userSettingRepository->findByKey($settingKey) as $userSetting) {
            if ($this->matches($userSetting, $entity, $scalarFields)) {
                $subscribers[] = $userSetting->user;
            }
        }

        return $subscribers;
    }

    /**
     * @param string[] $scalarFields
     */
    private function matches(UserSetting $userSetting, object $entity, array $scalarFields): bool
    {
        $matchFilters = 0;
        foreach ($userSetting->settings as $field => $values) {
            foreach ($values as $value) {
                $value = \in_array($field, $scalarFields, true)
                    ? $value
                    : $this->iriConverter->getResourceFromIri($value);

                if ($this->propertyAccessor->isReadable($entity, $field)
                    && $value === $this->propertyAccessor->getValue($entity, $field)
                ) {
                    ++$matchFilters;
                    break;
                }
            }
        }

        return $matchFilters === \count($userSetting->settings);
    }
}

<?php

declare(strict_types=1);

namespace App\Agile\Resources;

use Symfony\Component\Serializer\Annotation\SerializedPath;

class User
{
    public const LOGIN_METHOD = 'email';
    #[SerializedPath('[positions][0][manager][id]')]
    public ?string $managerAgileId = '';
    #[SerializedPath('[ref]')]
    public ?string $peopleId = '';
    #[SerializedPath('[positions][0][title]')]
    public ?string $jobTitle = '';
    public ?string $firstName = '';
    public ?string $lastName = '';
    public ?string $managerPeopleId = '';
    #[SerializedPath('[timeZone]')]
    public ?string $timeZone = 'America/New_York';
    public ?string $languageCode = 'fr';
    #[SerializedPath('[additionalFields][position]')]
    public ?string $position = '';
    #[SerializedPath('[additionalFields][department]')]
    public ?string $department = '';
    #[SerializedPath('[additionalFields][region]')]
    public ?string $region = '';
    #[SerializedPath('[additionalFields][businessunit]')]
    public ?string $businessunit = '';
    #[SerializedPath('[additionalFields][subdivision]')]
    public ?string $subdivision = '';
    #[SerializedPath('[additionalFields][division]')]
    public ?string $division = '';
    #[SerializedPath('[additionalFields][address]')]
    public ?string $address = '';
    #[SerializedPath('[additionalFields][contracttype]')]
    public ?string $contracttype = '';
    #[SerializedPath('[additionalFields][street1]')]
    public ?string $street1 = '';
    #[SerializedPath('[additionalFields][street2]')]
    public ?string $street2 = '';
    #[SerializedPath('[additionalFields][city]')]
    public ?string $city = '';
    #[SerializedPath('[additionalFields][country]')]
    public ?string $country = '';
    #[SerializedPath('[additionalFields][zipcode]')]
    public ?string $zipcode = '';
    public ?string $email = '';
    /** Necessary to know if we have to send $startDate and $timeZone at creation on agile */
    public bool $isNew = false;
    public ?string $loginMethod = self::LOGIN_METHOD;
    #[SerializedPath('[additionalFields][positioncategory]')]
    public ?string $positionCategory = '';
    #[SerializedPath('[status]')]
    private bool $active = false;

    #[SerializedPath('[positions][0][startDate]')]
    private string $startDate = '';

    public function getFormatedStartDate(string $format): ?string
    {
        return '' !== $this->getStartDate() ? (new \DateTime($this->startDate))->format($format) : '';
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(string $startDate): void
    {
        $this->startDate = '' !== $startDate ? (new \DateTime($startDate))->format('Y-m-d') : '';
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(string $status): void
    {
        $this->active = !('inactive' === $status);
    }
}

<?php

declare(strict_types=1);

namespace App\Javelo\Resources;

use Symfony\Component\Serializer\Annotation\SerializedPath;

class User
{
    public const DEFAULT_LOCALE = 'en';
    public const VALID_LOCALE = ['fr', 'en'];

    public ?string $id = null;
    public ?string $externalId = null;
    public ?string $userName = null;
    #[SerializedPath('[name][familyName]')]
    public ?string $familyName = null;
    #[SerializedPath('[name][givenName]')]
    public ?string $givenName = null;
    public bool $active = false;
    public ?string $locale = null;
    public ?string $title = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:enterprise:2.0:User][department]')]
    public ?string $department = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:javelo:2.0:User][managerUserName]')]
    public ?string $managerUserName = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:javelo:2.0:User][intranetId]')]
    public ?string $intranetId = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:javelo:2.0:User][businessUnit]')]
    public ?string $businessUnit = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:javelo:2.0:User][region]')]
    public ?string $region = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:javelo:2.0:User][subdivision]')]
    public ?string $subdivision = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:javelo:2.0:User][division]')]
    public ?string $division = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:javelo:2.0:User][gender]')]
    public ?string $gender = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:javelo:2.0:User][contractType]')]
    public ?string $contractType = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:javelo:2.0:User][position]')]
    public ?string $position = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:javelo:2.0:User][workingTime]')]
    public ?string $workingTime = null;
    #[SerializedPath('[urn:ietf:params:scim:schemas:extension:javelo:2.0:User][lastExitDate]')]
    private ?string $lastExitDate = null;

    public function getLastExitDate(): ?string
    {
        return $this->lastExitDate;
    }

    public function setLastExitDate(?string $lastExitDate): void
    {
        $this->lastExitDate = null !== $lastExitDate ? (new \DateTime($lastExitDate))->format('Y-m-d') : null;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }
}

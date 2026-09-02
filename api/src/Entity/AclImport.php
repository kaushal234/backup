<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Directory\Location;
use Symfony\Component\Validator\Constraints as Assert;

class AclImport
{
    /**
     * @var Acl[]
     */
    #[Assert\Count(min: 1, minMessage: 'You must specify at least one Acl')]
    private array $acls = [];

    private ?Location $location = null;

    public function __construct()
    {
    }

    public function getAcls(): array
    {
        return $this->acls;
    }

    /**
     * @param Acl[]|array $acls
     */
    public function setAcls(array $acls): self
    {
        $this->acls = $acls;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location = null): void
    {
        $this->location = $location;
    }
}

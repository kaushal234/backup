<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Directory\Location;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerGetterInterface;

interface SupplierEntityInterface extends BusinessPartnerGetterInterface
{
    public function getSupplierNumber(): ?string;

    public function setSupplierName(string $name): ?self;

    public function getLocation(): ?Location;
}

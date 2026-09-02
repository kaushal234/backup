<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerGetterInterface;

/**
 * @todo Delete for ln-compatibility. Don't forget to delete implementation, method and property group on the concerned entities
 */
interface LegacySupplierEntityInterface extends BusinessPartnerGetterInterface
{
    public function getSupplierErp(): ?int;
}

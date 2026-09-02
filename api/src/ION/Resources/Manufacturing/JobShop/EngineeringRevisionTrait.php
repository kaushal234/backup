<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use App\ION\Resources\Manufacturing\ItemRevisionStatus;
use Symfony\Component\Serializer\Attribute\Groups;

trait EngineeringRevisionTrait
{
    #[Groups(['ion:engineering:revision'])]
    public string $engineeringRevision;

    #[Groups(['ion:engineering:revision'])]
    public string $engineeringRevisionEffectiveDate;

    #[Groups(['ion:engineering:revision'])]
    public string $engineeringRevisionExpiryDate;

    #[Groups(['ion:engineering:revision'])]
    public function isExpired(): bool
    {
        return ItemRevisionStatus::expired($this->engineeringRevisionExpiryDate);
    }
}

<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing;

final class ItemRevisionStatus
{
    public static function expired(?string $engineeringRevisionExpiryDate): bool
    {
        if (empty($engineeringRevisionExpiryDate)) {
            return false;
        }

        return new \DateTime($engineeringRevisionExpiryDate) > new \DateTime('1980-01-01') && new \DateTime($engineeringRevisionExpiryDate) < new \DateTime();
    }
}

<?php

declare(strict_types=1);

namespace App\Report\Handler;

use App\Entity\Directory\People;

trait IsGrantedTrait
{
    public function isGranted(?object $user = null): bool
    {
        return $user instanceof People;
    }
}

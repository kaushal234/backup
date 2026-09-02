<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use App\Doctrine\OwnerReflectionBag;

interface TransferHandlerInterface
{
    public function handle(?object $source, object $target, OwnerReflectionBag $relation, array $conditions = []): void;

    public function getName(): string;
}

<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing;

use Symfony\Component\Serializer\Attribute\Groups;

class InventoryFile
{
    /**
     * Index of a file, useful to access image route.
     */
    #[Groups(['inventory_file'])]
    public int $key;

    #[Groups(['inventory_file'])]
    public string $uri;

    #[Groups(['inventory_file'])]
    public string $filename;

    #[Groups(['inventory_file'])]
    public ?string $extension = null;

    #[Groups(['inventory_file'])]
    public ?int $size = null;
}

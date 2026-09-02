<?php

declare(strict_types=1);

namespace App\ION\Resources;

use Symfony\Component\Serializer\Attribute\Groups;

class Enumeration
{
    #[Groups(['enumeration'])]
    public string $code;

    #[Groups(['enumeration'])]
    public string $constant;
}

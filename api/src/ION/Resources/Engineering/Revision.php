<?php

declare(strict_types=1);

namespace App\ION\Resources\Engineering;

use Symfony\Component\Serializer\Attribute\Groups;

class Revision
{
    #[Groups(['engineering:revision'])]
    public string $revision;

    #[Groups(['engineering:revision'])]
    public string $effectiveDate;

    #[Groups(['engineering:revision'])]
    public string $expiryDate;
}

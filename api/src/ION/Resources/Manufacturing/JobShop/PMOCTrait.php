<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use Symfony\Component\Serializer\Attribute\Groups;

trait PMOCTrait
{
    #[Groups(['ion:pmoc'])]
    public string $pmoc;

    #[Groups(['ion:pmoc'])]
    public bool $preventive;

    #[Groups(['ion:pmoc'])]
    public bool $maintenance;

    #[Groups(['ion:pmoc'])]
    public bool $overhaul;

    #[Groups(['ion:pmoc'])]
    public bool $critical;
}

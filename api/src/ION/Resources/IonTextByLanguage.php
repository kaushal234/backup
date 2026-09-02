<?php

declare(strict_types=1);

namespace App\ION\Resources;

use Symfony\Component\Serializer\Attribute\Groups;

class IonTextByLanguage
{
    #[Groups(['ion:text'])]
    public string $lang;

    #[Groups(['ion:text'])]
    public string $name;

    #[Groups(['ion:text'])]
    public string $text;
}

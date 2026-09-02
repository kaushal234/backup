<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing;

use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @deprecated
 */
class TextItemLang
{
    #[Groups(['inventory'])]
    public string $lang;

    #[Groups(['inventory'])]
    public string $name;

    #[Groups(['inventory'])]
    public array $texts = [];

    public function getTexts(): array
    {
        return $this->texts;
    }

    public function addText(string $text): self
    {
        $this->texts[] = $text;

        return $this;
    }

    public function removeText(string $text): self
    {
        return $this;
    }
}

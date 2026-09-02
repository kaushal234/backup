<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement\Orders;

use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @deprecated
 */
class LineText
{
    #[Groups(['purchase_order:details'])]
    public int $langCode;

    #[Groups(['purchase_order:details'])]
    public string $lang;

    #[Groups(['purchase_order:details'])]
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

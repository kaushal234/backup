<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing;

use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @deprecated
 */
class TextItem
{
    #[Groups(['inventory'])]
    public string $code;

    #[Groups(['inventory'])]
    public string $date;

    #[Groups(['inventory'])]
    public string $site;

    #[Groups(['inventory'])]
    protected array $textItemLangs = [];

    public function getTextItemLangs(): array
    {
        return $this->textItemLangs;
    }

    public function addTextItemLang(TextItemLang $textItemLang): self
    {
        $this->textItemLangs[] = $textItemLang;

        return $this;
    }

    public function removeTextItemLang(TextItemLang $textItemLang): self
    {
        return $this;
    }
}

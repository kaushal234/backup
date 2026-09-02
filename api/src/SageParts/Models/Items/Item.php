<?php

declare(strict_types=1);

namespace App\SageParts\Models\Items;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Item
{
    #[SerializedName('MasterID')]
    private string $masterID;

    #[SerializedName('@qty')]
    private int $quantity;

    #[SerializedName('@unit')]
    private string $unit;

    #[SerializedName('@lineNumber')]
    private int $lineNumber;

    public function getMasterID(): string
    {
        return $this->masterID;
    }

    public function setMasterID(string $masterID): self
    {
        $this->masterID = $masterID;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    public function setLineNumber(int $lineNumber): self
    {
        $this->lineNumber = $lineNumber;

        return $this;
    }
}

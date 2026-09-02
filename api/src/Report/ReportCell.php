<?php

declare(strict_types=1);

namespace App\Report;

class ReportCell
{
    private readonly string $x;

    private readonly string $y;

    private readonly float $value;

    private array $extraData;

    public function __construct(string $x, string $y, float $value, array $extraData = [])
    {
        $this->x = $x;
        $this->y = $y;
        $this->value = $value;
        $this->extraData = $extraData;
    }

    public function getX(): string
    {
        return $this->x;
    }

    public function getY(): string
    {
        return $this->y;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getExtraData(): array
    {
        return $this->extraData;
    }
}

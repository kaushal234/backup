<?php

declare(strict_types=1);

namespace App\Report;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\DataProvider\ReportDataProvider;
use App\Filter\ReportOptionsFilter;

#[ApiResource(
    operations: [
        new Get(
            requirements: ['id' => '.+'],
            provider: ReportDataProvider::class),
    ],
    security: 'is_granted("ACCESS_PEOPLE") or is_granted("ACCESS_VENDOR_USER")',
)]
#[ApiFilter(ReportOptionsFilter::class)]
class Report
{
    #[ApiProperty(identifier: true)]
    private readonly string $resource;

    #[ApiProperty(identifier: true)]
    private readonly string $x;

    #[ApiProperty(identifier: true)]
    private readonly string $y;

    private array $xTotals = [];

    private array $yTotals = [];

    private float $total = 0.0;

    private int $totalIncrement = 0;

    private array $rows = [];

    private array $metadata = [];

    public function __construct(string $resource, string $x, string $y)
    {
        $this->resource = $resource;
        $this->x = $x;
        $this->y = $y;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getX(): string
    {
        return $this->x;
    }

    public function getY(): string
    {
        return $this->y;
    }

    public function getxTotals(): array
    {
        return $this->xTotals;
    }

    public function getyTotals(): array
    {
        return $this->yTotals;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function addCell(string $x, string $y, float $amount, array $extraData = []): self
    {
        if (!\array_key_exists($x, $this->rows)) {
            $this->rows[$x] = [];
        }

        $this->rows[$x][$y] = new ReportCell($x, $y, $amount, $extraData);

        $this->addToTotals($x, $y, $amount);

        return $this;
    }

    public function cleanUp(): void
    {
        ksort($this->xTotals);
        ksort($this->yTotals);
        foreach ($this->rows as $x => &$cells) {
            foreach (array_keys($this->yTotals) as $yLabel) {
                if (!isset($cells[$yLabel])) {
                    $cells[$yLabel] = new ReportCell((string) $x, (string) $yLabel, 0);
                }
            }
            ksort($cells);
        }
    }

    /** @param string|int $title */
    public function addMetadata($title, $metadata): self
    {
        $this->metadata[$title] = $metadata;

        return $this;
    }

    public function renderTotalsAsAverages(): void
    {
        foreach ($this->xTotals as $x => $total) {
            $this->xTotals[$x] = round($this->xTotals[$x] / \count($this->rows[$x]), 2);
        }

        foreach ($this->yTotals as $y => $total) {
            $this->yTotals[$y] = round($this->yTotals[$y] / \count($this->rows), 2);
        }

        $this->total = !$this->totalIncrement ? $this->total : round($this->total / $this->totalIncrement, 2);
    }

    private function addToTotals(string $x, string $y, float $amount): void
    {
        if (!\array_key_exists($x, $this->xTotals)) {
            $this->xTotals[$x] = 0;
        }

        if (!\array_key_exists($y, $this->yTotals)) {
            $this->yTotals[$y] = 0;
        }

        $this->xTotals[$x] += $amount;
        $this->yTotals[$y] += $amount;
        $this->total += $amount;
        ++$this->totalIncrement;
    }
}

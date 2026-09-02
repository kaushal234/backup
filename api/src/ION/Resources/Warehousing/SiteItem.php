<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing;

use Symfony\Component\Serializer\Attribute\Groups;

class SiteItem
{
    #[Groups(['inventory'])]
    public string $site;

    #[Groups(['inventory'])]
    public string $leadtime;

    #[Groups(['inventory'])]
    public string $codeSignal;

    #[Groups(['inventory'])]
    public float $weight;

    #[Groups(['inventory'])]
    public string $weightUnitOfMeasure;

    #[Groups(['inventory'])]
    public ?TextItem $textItem = null;

    #[Groups(['inventory'])]
    protected array $warehouses = [];

    /**
     * @return array<Warehouse>
     */
    public function getWarehouses(): array
    {
        return $this->warehouses;
    }

    public function addWarehouse(Warehouse $warehouse): self
    {
        $this->warehouses[] = $warehouse;

        return $this;
    }

    public function removeWarehouse(Warehouse $warehouse): self
    {
        return $this;
    }
}

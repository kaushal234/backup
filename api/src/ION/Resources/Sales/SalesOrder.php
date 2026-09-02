<?php

declare(strict_types=1);

namespace App\ION\Resources\Sales;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['sales_order', 'site:light']],
    denormalizationContext: ['groups' => []],
    provider: CachedIONItemDataProvider::class,
)]
class SalesOrder
{
    #[ApiProperty(identifier: true)]
    #[Groups(['sales_order'])]
    public string $salesOrder;

    /**
     * @var SalesOrderLine[]
     */
    #[Groups(['sales_order'])]
    private array $lines = [];

    public function addLine(SalesOrderLine $line): self
    {
        $this->lines[] = $line;

        return $this;
    }

    public function removeLine(SalesOrderLine $line): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }

    public function getLines(): array
    {
        return $this->lines;
    }
}

<?php

declare(strict_types=1);

namespace App\SageParts\P21\ResourceSourceProvider;

use App\SageParts\P21\Resources\Supplier;

class SupplierResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getOperation(): ?string
    {
        return 'SCAR_view_supplier';
    }

    public function getFieldMapping(): array
    {
        return ['name' => 'supplier_name', 'code' => 'SupplierId'];
    }

    public function supports(string $class): bool
    {
        return Supplier::class === $class;
    }
}

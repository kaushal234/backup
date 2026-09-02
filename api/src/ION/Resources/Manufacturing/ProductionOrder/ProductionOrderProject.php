<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\ProductionOrder;

use App\ION\DataProcessor\IONDataProcessor;
use Symfony\Component\Serializer\Attribute\Groups;

class ProductionOrderProject
{
    #[Groups(['production_order:write', 'production_order:view', IONDataProcessor::ION_SYNC])]
    public string $code;

    #[Groups(['production_order:write', 'production_order:view', IONDataProcessor::ION_SYNC])]
    public int $openCrabs;

    #[Groups(['production_order:write', 'production_order:view', IONDataProcessor::ION_SYNC])]
    public int $totalCrabs;

    #[Groups(['production_order:view'])]
    public string $status;
}

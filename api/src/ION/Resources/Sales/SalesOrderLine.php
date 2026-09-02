<?php

declare(strict_types=1);

namespace App\ION\Resources\Sales;

use ApiPlatform\Metadata\ApiProperty;
use App\ION\Resources\MasterData\EnterpriseModel\EnterpriseStructure\Site;
use Symfony\Component\Serializer\Attribute\Groups;

class SalesOrderLine
{
    #[ApiProperty(identifier: true)]
    #[Groups(['sales_order'])]
    public string $lineID;

    #[Groups(['sales_order'])]
    public string $item;

    #[Groups(['sales_order'])]
    public \DateTime $plannedDeliveryDate;

    #[Groups(['sales_order'])]
    public Site $site;
}

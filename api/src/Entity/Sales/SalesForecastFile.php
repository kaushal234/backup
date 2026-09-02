<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'sales_forecasts_files')]
#[App\Loggable(owner: 'salesForecast', ownerRelation: 'salesForecastFiles')]
class SalesForecastFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\SalesForecast', inversedBy: 'salesForecastFiles')]
    private ?SalesForecast $salesForecast = null;

    public function getSalesForecast(): SalesForecast
    {
        return $this->salesForecast;
    }

    public function setSalesForecast(SalesForecast $salesForecast): self
    {
        $this->salesForecast = $salesForecast;

        return $this;
    }
}

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
#[ORM\Table(name: 'forecast_closures_files')]
#[App\Loggable(owner: 'forecastClosure', ownerRelation: 'forecastClosureFiles')]
class ForecastClosureFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ForecastClosure', inversedBy: 'forecastClosureFiles')]
    private ?ForecastClosure $forecastClosure = null;

    public function getForecastClosure(): ForecastClosure
    {
        return $this->forecastClosure;
    }

    public function setForecastClosure(ForecastClosure $forecastClosure): self
    {
        $this->forecastClosure = $forecastClosure;

        return $this;
    }
}

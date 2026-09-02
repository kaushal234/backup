<?php

declare(strict_types=1);

namespace App\Dto\Sales\SalesForecast;

use App\Entity\Sales\SalesForecast;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class SalesForecastLinkTo
{
    #[Assert\NotNull]
    #[Groups(['sales_forecast:link'])]
    public ?SalesForecast $salesForecastToLink = null;
}

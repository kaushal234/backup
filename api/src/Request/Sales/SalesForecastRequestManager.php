<?php

declare(strict_types=1);

namespace App\Request\Sales;

use App\Entity\Sales\SalesForecast;
use App\Request\SubRequestManager;
use Symfony\Component\HttpFoundation\Request;

class SalesForecastRequestManager
{
    private readonly SubRequestManager $subRequestManager;

    public function __construct(SubRequestManager $subRequestManager)
    {
        $this->subRequestManager = $subRequestManager;
    }

    public function updateStatus(SalesForecast $salesForecast, string $status, array $content = [])
    {
        $this->subRequestManager->doSubRequest('api_sales_forecast_update_status', ['id' => $salesForecast->getId()], Request::METHOD_PUT, [...$content, 'status' => $status]);
    }

    public function notify(SalesForecast $salesForecast)
    {
        $this->subRequestManager->doSubRequest('api_sales_forecasts_notify_item', [
            'id' => $salesForecast->getId(),
        ], Request::METHOD_PUT);
    }
}

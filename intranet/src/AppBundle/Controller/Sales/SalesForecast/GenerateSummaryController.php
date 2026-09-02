<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\SalesForecast;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/sales-forecasts')]
class GenerateSummaryController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/{id}/generate-summary', name: 'sales_forecast_generate_summary_modal', methods: ['GET'])]
    #[Template('sales/sales_forecasts/partial/modal/generate_summary.html.twig')]
    public function modal(#[ApiValueResolverAttribute(parameters: ['resource' => SalesForecastController::RESOURCE_URL])] ApiData $salesForecast): array
    {
        try {
            $result = $this->client->get(\sprintf('ai/summarize/sales_forecasts/%s', $salesForecast->getIriId()));
            $summary = $result['summary'];
        } catch (ClientException $exception) {
            $summary = $this->translator->trans('sales_forecast.error.summary', [], 'sales_forecasts');
        }

        return [
            'id' => $salesForecast->getIriId(),
            'summary' => $summary,
        ];
    }
}

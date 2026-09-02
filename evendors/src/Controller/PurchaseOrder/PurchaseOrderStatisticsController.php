<?php

declare(strict_types=1);

namespace App\Controller\PurchaseOrder;

use App\Statistic\Provider\PurchaseOrderStatisticProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final readonly class PurchaseOrderStatisticsController
{
    public function __construct(
        private PurchaseOrderStatisticProvider $statistic,
    ) {
    }

    /**
     * @return array<string, array<string, int<0, max>>>
     */
    #[Route('/PurchaseOrderStatistics', name: 'purchase-order:statistics', methods: [Request::METHOD_GET])]
    #[Template('purchase-order/purchase_order_statistics.html.twig')]
    public function __invoke(Request $request): array
    {
        return ['stats' => $this->statistic->getStatistic()];
    }
}

<?php

declare(strict_types=1);

namespace App\Statistic\Provider;

use App\CQRS\Query\PurchaseOrder\FindAllPurchaseOrderOpenQuery;
use App\CQRS\QueryBusInterface;
use App\Sdk\Resource\PurchaseOrderLine;

final class PurchaseOrderStatisticProvider
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    /**
     * @return array{Late: int<0, max>, Unconfirmed: int<0, max>, "To be delivered in 7 days": int<0, max>}
     */
    public function getStatistic(): array
    {
        $orders = $this->queryBus->dispatch(new FindAllPurchaseOrderOpenQuery());

        $lateLinesCount = 0;
        $unconfirmedLinesCount = 0;
        $toBeDeliveredIn7DaysLinesCount = 0;
        $total = 0;
        if (null !== $orders) {
            foreach ($orders as $order) {
                /** @var PurchaseOrderLine $line */
                foreach ($order->lines as $line) {
                    if ($line->late) {
                        ++$lateLinesCount;
                    }

                    if ($line->unconfirmed && $line->isConfirmable) {
                        ++$unconfirmedLinesCount;
                    }

                    if ($line->toBeDeliveredIn7Days) {
                        ++$toBeDeliveredIn7DaysLinesCount;
                    }
                    ++$total;
                }
            }
        }

        return [
            'Late' => $lateLinesCount,
            'Unconfirmed' => $unconfirmedLinesCount,
            'To be delivered in 7 days' => $toBeDeliveredIn7DaysLinesCount,
            'Total' => $total,
        ];
    }
}

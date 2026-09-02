<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Formatter;

use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Symfony\Component\Translation\TranslatableMessage;

class DateRangeActiveFilterFormatter
{
    public function __invoke(FilterData $data): TranslatableMessage|string
    {
        $value = $data->getValue();

        $dateFrom = $value['from'];
        $dateTo = $value['to'];

        if (\is_string($dateFrom)) {
            $dateFrom = \DateTime::createFromFormat('Y-m-d\TH:i:sO', $dateFrom);
        }

        if (\is_string($dateTo)) {
            $dateTo = \DateTime::createFromFormat('Y-m-d\TH:i:sO', $dateTo);
        }

        if (null !== $dateFrom && null === $dateTo) {
            return new TranslatableMessage('After %date%', ['%date%' => $dateFrom->format('Y-m-d')], 'KreyuDataTable');
        }

        if (null === $dateFrom && null !== $dateTo) {
            return new TranslatableMessage('Before %date%', ['%date%' => $dateTo->format('Y-m-d')], 'KreyuDataTable');
        }

        if ($dateFrom === $dateTo) {
            return $dateFrom->format('Y-m-d');
        }

        return $dateFrom->format('Y-m-d').' - '.$dateTo->format('Y-m-d');
    }
}

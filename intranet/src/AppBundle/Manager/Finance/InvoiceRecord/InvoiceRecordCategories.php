<?php

declare(strict_types=1);

namespace AppBundle\Manager\Finance\InvoiceRecord;

final class InvoiceRecordCategories
{
    public static function getCategories()
    {
        return [
            'INSOLVENCY' => 'INSOLVENCY',
            'TECHNICAL PROBLEM' => 'TECHNICAL PROBLEM',
            'ADMINISTRATIVE PROBLEM' => 'ADMINISTRATIVE PROBLEM',
            'SERIOUS DISPUTE' => 'SERIOUS DISPUTE',
            'PAYMENT DEFERRAL' => 'PAYMENT DEFERRAL',
            'DISPUTE' => 'DISPUTE',
            'OTHER' => 'OTHER',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filter\Finance;

use ApiPlatform\Metadata\FilterInterface;

class AccountReceivableOverviewFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            'customerErpReference.sso' => ['property' => 'customerErpReference.sso', 'type' => 'string', 'required' => false],
            'currency' => ['property' => 'currency', 'type' => 'string', 'required' => false],
            'transactionTypeReference.transactionType' => ['property' => 'transactionTypeReference.transactionType', 'type' => 'string', 'required' => false],
            'customerErpReference.customer.mainSalesRepresentative.asm' => ['property' => 'customerErpReference.customer.mainSalesRepresentative.asm', 'type' => 'string', 'required' => false],
            'customerErpReference.customer.mainSalesRepresentative.asm.supervisor' => ['property' => 'customerErpReference.customer.mainSalesRepresentative.asm.supervisor', 'type' => 'string', 'required' => false],
        ];
    }
}

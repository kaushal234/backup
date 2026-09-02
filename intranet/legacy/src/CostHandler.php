<?php

declare(strict_types=1);

namespace Legacy;

use Legacy\Provider\CustomerServiceRecordProvider;

class CostHandler
{
    private CustomerServiceRecordProvider $customerServiceRecordProvider;

    public function __construct()
    {
        $this->customerServiceRecordProvider = new CustomerServiceRecordProvider();
    }

    public function getLegacyId(string $module, int $legacyId)
    {
        return match ($module) {
            'CSR' => $this->customerServiceRecordProvider->fineByLegacyId($legacyId)->getIriId(),
            default => $legacyId,
        };

    }
}
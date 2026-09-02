<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Location extends Constraint
{
    public function __construct(
        public readonly string $errorMessage = 'This TLD location is not a {{ capability }}.',
        public readonly string $errorMessageErpInBaan = 'This location ERP is not in Baan.',
        public readonly bool $sso = false,
        public readonly bool $factory = false,
        public readonly bool $warehouse = false,
        public readonly bool $sparePartsHub = false,
        public readonly bool $serviceHub = false,
        public readonly bool $erpInLN = false,
        public readonly string $messageCustomerNumberNotValid = 'The customer {{ value }} does not exist in ERP {{ erp }}.',
        public readonly mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(
            options: $options,
            groups: $groups,
            payload: $payload,
        );
    }
}

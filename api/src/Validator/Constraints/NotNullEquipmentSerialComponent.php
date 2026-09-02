<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints\NotNull;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class NotNullEquipmentSerialComponent extends NotNull
{
    public function __construct(
        string $message = 'Components on Equipment Record Serial list have to be edited: https://www.tld-gse.com/en/private/support/serials/{{ equipmentRecordId }}/show',
        $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $message, $groups, $payload);
    }
}

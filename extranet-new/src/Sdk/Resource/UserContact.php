<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

class UserContact
{
    public function __construct(
        public readonly ?string $reception = null,
        public readonly ?string $phone = null,
        public readonly ?string $mobile = null,
        public readonly ?string $fax = null,
    ) {
    }
}

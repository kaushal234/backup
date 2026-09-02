<?php

declare(strict_types=1);

namespace App\AI\Dto\Directory;

final readonly class PeopleModel
{
    public function __construct(
        public string $username,
        public string $email,
        public ?string $firstname,
        public ?string $lastname,
    ) {
    }
}

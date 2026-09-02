<?php

declare(strict_types=1);

namespace App\AI\Dto\Activity;

final readonly class CommentModel
{
    public function __construct(
        public string $message,
        public \DateTimeInterface $createdAt,
        public ?string $authorEmail,
        public ?string $authorFirstname,
        public ?string $authorLastname,
    ) {
    }
}

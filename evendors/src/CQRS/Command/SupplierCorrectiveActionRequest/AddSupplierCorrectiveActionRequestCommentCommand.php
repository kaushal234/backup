<?php

declare(strict_types=1);

namespace App\CQRS\Command\SupplierCorrectiveActionRequest;

use App\CQRS\Command\CommandInterface;

final class AddSupplierCorrectiveActionRequestCommentCommand implements CommandInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $message,
        public readonly ?string $file,
    ) {
    }
}

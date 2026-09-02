<?php

declare(strict_types=1);

namespace App\CQRS\Query\TechnicianOnCall;

use App\CQRS\Query\QueryInterface;

final class ExportTechnicianOnCallQuery implements QueryInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly string $filename,
        public readonly string $format,
        public readonly array $options = [],
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\AI\Dto;

final class SearchOutput
{
    public ?string $logIri = null;

    /**
     * @var array<Result>
     */
    public array $results;
}

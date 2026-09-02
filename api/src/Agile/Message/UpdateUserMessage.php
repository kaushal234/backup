<?php

declare(strict_types=1);

namespace App\Agile\Message;

final class UpdateUserMessage
{
    public function __construct(public readonly string $peopleIri)
    {
    }
}

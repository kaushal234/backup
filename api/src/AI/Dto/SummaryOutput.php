<?php

declare(strict_types=1);

namespace App\AI\Dto;

use Symfony\Component\Serializer\Attribute\Groups;

final class SummaryOutput
{
    #[Groups(groups: ['summary'])]
    public ?string $logIri = null;

    #[Groups(groups: ['summary'])]
    public string $summary;
}

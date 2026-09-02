<?php

declare(strict_types=1);

namespace App\AI\Dto\Service;

use Symfony\Component\Serializer\Attribute\Groups;

final class ClosureSuggestionOutput
{
    #[Groups(['closure_suggestion'])]
    public ?string $symptoms = null;

    #[Groups(['closure_suggestion'])]
    public ?string $rootCause = null;

    #[Groups(['closure_suggestion'])]
    public ?string $solution = null;
}

<?php

declare(strict_types=1);

namespace App\AI\DataProvider\TechnicianOnCall;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\AI\Service\Generator\ClosureSuggestionGenerator;

final readonly class ClosureSuggestionDataProvider implements ProviderInterface
{
    public function __construct(
        private ClosureSuggestionGenerator $generator,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object
    {
        return $this->generator->generate((int) $uriVariables['id']);
    }
}

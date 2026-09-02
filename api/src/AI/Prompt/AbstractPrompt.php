<?php

declare(strict_types=1);

namespace App\AI\Prompt;

abstract class AbstractPrompt implements PromptInterface
{
    public function __construct(
        private readonly string $source,
        private readonly array $options = [],
    ) {
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}

<?php

declare(strict_types=1);

namespace App\AI\Prompt;

interface PromptInterface
{
    public function instructions(): string;

    public function getSource(): string;

    public function getOptions(): array;
}

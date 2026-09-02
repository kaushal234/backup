<?php

declare(strict_types=1);

namespace App\AI\Platform;

use App\AI\Prompt\PromptInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

readonly class TextPlatform extends AbstractPlatform
{
    public function ask(PromptInterface $prompt, string $text, bool $createLog = false, string $model = 'mistral-small-latest'): PlatformResult
    {
        return $this->invoke(
            messageBag: new MessageBag(
                Message::forSystem('You are an expert assistant.'),
                Message::ofUser(\sprintf('%s: %s', $prompt->instructions(), $text))
            ),
            source: $prompt->getSource(),
            options: $prompt->getOptions(),
            model: $model,
            createLog: $createLog,
        );
    }
}

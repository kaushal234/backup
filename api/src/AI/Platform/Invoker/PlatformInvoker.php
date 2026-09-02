<?php

declare(strict_types=1);

namespace App\AI\Platform\Invoker;

use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PlatformInvoker implements PlatformInvokerInterface
{
    public function __construct(
        #[Autowire(service: 'ai.platform.mistral')]
        private PlatformInterface $platform,
    ) {
    }

    public function invokeAsText(string $model, MessageBag $messageBag): string
    {
        return $this->platform->invoke(model: $model, input: $messageBag)->asText();
    }
}

<?php

declare(strict_types=1);

namespace App\AI\Platform\Invoker;

use Symfony\AI\Platform\Message\MessageBag;

interface PlatformInvokerInterface
{
    public function invokeAsText(string $model, MessageBag $messageBag): string;
}

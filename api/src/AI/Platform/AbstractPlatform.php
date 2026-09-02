<?php

declare(strict_types=1);

namespace App\AI\Platform;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Factory\AILogFactory;
use App\AI\Platform\Invoker\PlatformInvokerInterface;
use Symfony\AI\Platform\Message\MessageBag;

abstract readonly class AbstractPlatform
{
    public function __construct(
        private PlatformInvokerInterface $invoker,
        private AILogFactory $factory,
        private IriConverterInterface $iriConverter,
    ) {
    }

    final protected function invoke(MessageBag $messageBag, string $source, array $options = [], string $model = 'mistral-small-latest', bool $createLog = false): PlatformResult
    {
        $request = $this->factory->createRequest($source, $options);
        $result = $this->invoker->invokeAsText(model: $model, messageBag: $messageBag);

        $log = null;
        if ($createLog) {
            $log = $this->factory->createLog($request, $result);
        }

        return new PlatformResult(
            result: $result,
            logIri: null !== $log ? $this->iriConverter->getIriFromResource($log) : null,
        );
    }
}

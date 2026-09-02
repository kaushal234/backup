<?php

declare(strict_types=1);

namespace App\AI\DataProvider\Summarize;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\AI\Exception\AccessDeniedException;
use App\AI\Exception\EntityNotFoundException;
use App\AI\Service\Summarizer\GenericSummarizer;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[FeatureDoc(path: 'ai-summarizer.md')]
readonly class SummarizeDataProvider implements ProviderInterface
{
    public function __construct(
        private GenericSummarizer $summarizer,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        try {
            return $this->summarizer->summarize($operation->getClass(), $uriVariables, true);
        } catch (EntityNotFoundException $exception) {
            return null;
        } catch (AccessDeniedException $exception) {
            throw new AccessDeniedHttpException('Access denied');
        }
    }
}

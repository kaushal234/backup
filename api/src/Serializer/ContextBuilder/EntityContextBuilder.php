<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class EntityContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;

    public function __construct(SerializerContextBuilderInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $context[AbstractObjectNormalizer::ENABLE_MAX_DEPTH] = true;
        if (\in_array('disable_max_depth', $context[AbstractObjectNormalizer::GROUPS] ?? [], true)) {
            unset($context[AbstractObjectNormalizer::ENABLE_MAX_DEPTH]);
        }

        return $context;
    }
}

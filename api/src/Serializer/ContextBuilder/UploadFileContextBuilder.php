<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class UploadFileContextBuilder implements SerializerContextBuilderInterface
{
    /** @var string[] */
    private const FILE_GROUPS = ['file', 'people_public', 'expose_legacy'];

    private readonly SerializerContextBuilderInterface $decorated;

    public function __construct(SerializerContextBuilderInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (!str_starts_with($context['operation_name'] ?? '', 'upload')) {
            return $context;
        }

        $context['skip_null_values'] = false;
        $context[AbstractObjectNormalizer::GROUPS] = self::FILE_GROUPS;

        return $context;
    }
}

<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Sales\ExtranetUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class ExtranetUserPasswordContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;

    private readonly DecoderInterface $decoder;

    public function __construct(SerializerContextBuilderInterface $decorated, DecoderInterface $decoder)
    {
        $this->decorated = $decorated;
        $this->decoder = $decoder;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if ($normalization || ExtranetUser::class !== $context['resource_class']) {
            return $context;
        }

        $content = $this->decoder->decode((string) $request->getContent(), JsonEncoder::FORMAT);

        if ($content['password'] ?? false) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'token_write';
        }

        return $context;
    }
}

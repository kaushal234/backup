<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\AI;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\AI\AILog;
use App\Mercure\TokenGenerator;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AILogNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'AI_LOG_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly TokenGenerator $tokenGenerator,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AILog && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param AILog $data
     *
     * @throws ExceptionInterface
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($data, $format, $context);

        if (!($context['operation'] ?? null) instanceof Get) {
            return $normalizedData;
        }

        $iri = $this->iriConverter->getIriFromResource($data);
        $token = $this->tokenGenerator->generateForTopic($iri);

        if ('chat' === $data->type) {
            $normalizedData['mercure'] = ['token' => $token];
        }

        return $normalizedData;
    }
}

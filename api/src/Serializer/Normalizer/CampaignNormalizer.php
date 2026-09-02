<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use ApiPlatform\Metadata\Get;
use App\Entity\Survey\Campaign;
use App\Manager\Survey\PublishedSurveyManager;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampaignNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'CAMPAIGN_NORMALIZER_ALREADY_CALLED';
    private readonly PublishedSurveyManager $publishedSurveyManager;

    public function __construct(PublishedSurveyManager $publishedSurveyManager)
    {
        $this->publishedSurveyManager = $publishedSurveyManager;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Campaign && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param Campaign $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if (!($context['operation'] ?? null) instanceof Get) {
            return $normalizedData;
        }

        $normalizedData['results'] = $this->publishedSurveyManager->processCampaignResults($object);

        return $normalizedData;
    }
}

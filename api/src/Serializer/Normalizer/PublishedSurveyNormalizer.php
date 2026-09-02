<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Survey\PublishedSurvey;
use App\Survey\SurveyItemsParser;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedSurveyNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'PUBLISHED_SURVEY_NORMALIZER_ALREADY_CALLED';
    private readonly SurveyItemsParser $parser;

    public function __construct(SurveyItemsParser $parser)
    {
        $this->parser = $parser;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PublishedSurvey && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param PublishedSurvey $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $this->parser->processPublishedSurvey($object);

        $normalizedData['totalItemsAnswered'] = $object->getTotalItemsAnswered();
        $normalizedData['totalItems'] = $object->getTotalItems();
        $normalizedData['totalRemainingItems'] = $object->getTotalRemainingItems();

        return $normalizedData;
    }
}

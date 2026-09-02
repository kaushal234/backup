<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Quality;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use App\Entity\Quality\Crab;
use LegacyBundle\Manager\CrabManager;
use LegacyBundle\Manager\TaskManager;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CrabNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'CRAB_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly TaskManager $taskManager,
        private readonly CrabManager $crabManager,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Crab && (($context['operation'] ?? null) instanceof Get || ($context['operation'] ?? null) instanceof Put) && !\in_array('derogation:list', $context[AbstractNormalizer::GROUPS], true) && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param Crab $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $normalizedData['openTasks'] = !empty($this->taskManager->findOpenTasksByModule('CRAB', $object->getLegacyId()));
        $piQuestionDetails = $this->crabManager->getPiQuestionDetails($object);

        $normalizedData['piQuestionSubject'] = $piQuestionDetails['subject_en'] ?? null;
        $normalizedData['piQuestionDescription'] = $piQuestionDetails['desc_en'] ?? null;

        return $normalizedData;
    }
}

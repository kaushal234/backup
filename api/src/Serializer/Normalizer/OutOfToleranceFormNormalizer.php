<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Quality\CalibratedTools\OutOfToleranceForm;
use App\Workflow\WorkflowStatusParser;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OutOfToleranceFormNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'OUT_OF_TOLERANCE_FORM_NORMALIZER_ALREADY_CALLED';

    private readonly WorkflowStatusParser $parser;

    public function __construct(WorkflowStatusParser $parser)
    {
        $this->parser = $parser;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof OutOfToleranceForm && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param OutOfToleranceForm $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $normalizedData['availableStatuses'] = [...$this->parser->getAvailableStatuses($object), ...[$object->getStatus()]];

        return $normalizedData;
    }
}

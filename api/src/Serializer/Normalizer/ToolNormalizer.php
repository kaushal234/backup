<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Quality\CalibratedTools\CalibrationLog;
use App\Entity\Quality\CalibratedTools\Tool;
use App\Workflow\WorkflowStatusParser;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ToolNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'TOOL_NORMALIZER_ALREADY_CALLED';
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
        return $data instanceof Tool && null === ($context[self::ALREADY_CALLED] ?? null) && CsvEncoder::FORMAT !== $format;
    }

    /**
     * @param Tool $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $normalizedData += [
            'availableStatuses' => array_filter(
                $this->parser->getAvailableStatuses($object),
                static fn ($status) => !\in_array($status, [Tool::CALIBRATION_DUE_SOON, Tool::EXPIRED], true)
            ),
        ];

        $days = null;
        if (!$object->getCalibrationLogs()->isEmpty()) {
            /** @var CalibrationLog $lastCalibrationLog */
            $lastCalibrationLog = $object->getCalibrationLogs()->last();

            if (null !== $lastCalibrationDate = $lastCalibrationLog->getCalibrationDate()) {
                $lastCalibrationDate->modify(\sprintf('+%s day', $object->getCalibrationInterval()));
                $nextCalibrationDate = $object->getNextCalibrationDate() ?? $lastCalibrationDate;
                $diff = (new \DateTime())->diff($nextCalibrationDate);
                $days = (int) $diff->days * (1 === $diff->invert ? -1 : 1);
            }
        }
        $normalizedData['remainingDaysBeforeExpiration'] = $days;

        return $normalizedData;
    }
}

<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\TransitionBlocker;

final class WorkflowEnabledNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    public const NORMALIZATION_GROUP_NAME = 'workflow';

    /**
     * @var string
     */
    private const WORKFLOW_ALREADY_CHECKED = 'WORKFLOW_ALREADY_CHECKED';

    private readonly Registry $workflowRegistry;

    public function __construct(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return \in_array(self::NORMALIZATION_GROUP_NAME, $context[AbstractNormalizer::GROUPS] ?? [], true) && (false === ($context[self::WORKFLOW_ALREADY_CHECKED] ?? false));
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    /**
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $normalizedData = $this->normalizer->normalize($object, $format, $context + [self::WORKFLOW_ALREADY_CHECKED => $object]);
        if (!\is_array($normalizedData)) {
            return $normalizedData;
        }

        try {
            $workflow = $this->workflowRegistry->get($object);
        } catch (InvalidArgumentException $exception) {
            throw new UnprocessableEntityHttpException(\sprintf('Unable to find a workflow for class "%s".', $object::class), $exception);
        }

        $statuses = [];
        $blockers = [];
        $marking = $workflow->getMarking($object);
        foreach ($workflow->getDefinition()->getTransitions() as $transition) {
            $transitionName = $transition->getName();
            $fromCurrentPlace = false;

            foreach ($transition->getFroms() as $place) {
                if ($marking->has($place)) {
                    $fromCurrentPlace = $place;
                    break;
                }
            }

            if (!$fromCurrentPlace) {
                continue;
            }

            if ($workflow->can($object, $transitionName)) {
                $statuses[] = $transition->getTos();
                continue;
            }

            $blockerList = $workflow->buildTransitionBlockerList($object, $transitionName);

            foreach ($blockerList as $blocker) {
                if (TransitionBlocker::UNKNOWN === $blocker->getCode()) {
                    foreach ($transition->getTos() as $to) {
                        $blockers[] = [
                            'from' => $fromCurrentPlace,
                            'to' => $to,
                            'message' => $blocker->getMessage(),
                        ];
                    }
                }
            }
        }

        $normalizedData['availableStatus'] = array_merge(...$statuses);
        $normalizedData['statusBlockerMessage'] = array_values(array_map('unserialize', array_unique(array_map('serialize', $blockers))));

        return $normalizedData;
    }
}

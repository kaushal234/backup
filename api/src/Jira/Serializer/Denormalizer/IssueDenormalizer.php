<?php

declare(strict_types=1);

namespace App\Jira\Serializer\Denormalizer;

use ApiPlatform\Metadata\Get;
use App\Jira\Enum\CustomField;
use App\Jira\Resources\Issue;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class IssueDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'ISSUE_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;
        foreach (['startDate' => 'startDueDate', 'endDate' => 'endDueDate'] as $key => $value) {
            if (isset($data['fields'][CustomField::Sprint->value][\count($data['fields'][CustomField::Sprint->value] ?? []) - 1][$key])) {
                $data[$value] = $data['fields'][CustomField::Sprint->value][0][$key];
            }
        }

        $data['priority'] = $data['fields']['priority'];
        $data['status'] = $data['fields']['status']['name'] ?? null;
        $data['id'] = $data['key'];

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        if (($context[self::ALREADY_CALLED] ?? null) || !($context['operation'] ?? null) instanceof Get) {
            return false;
        }

        return is_subclass_of($type, Issue::class);
    }
}

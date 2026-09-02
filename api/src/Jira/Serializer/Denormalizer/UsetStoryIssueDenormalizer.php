<?php

declare(strict_types=1);

namespace App\Jira\Serializer\Denormalizer;

use ApiPlatform\Metadata\Get;
use App\Jira\Enum\CustomField;
use App\Jira\Resources\UserStoryIssue;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UsetStoryIssueDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'USER_STORY_ISSUE_DENORMALIZER_ALREADY_CALLED';

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;
        $data['userStoryId'] = isset($data['fields'][CustomField::UserStoryNumber->value]) ? (int) $data['fields'][CustomField::UserStoryNumber->value] : null;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        if (!($context[self::ALREADY_CALLED] ?? null) && ($context['operation'] ?? null) instanceof Get) {
            return UserStoryIssue::class === $type;
        }

        return false;
    }
}

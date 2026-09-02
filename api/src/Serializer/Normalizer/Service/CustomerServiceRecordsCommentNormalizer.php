<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\TOCManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CustomerServiceRecordsCommentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'CUSTOMER_SERVICE_RECORDS_COMMENT_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TOCManager $TOCManager,
        private readonly IriConverterInterface $iriConverter,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (!$data instanceof Comment) {
            return false;
        }

        if (!$this->requestStack->getCurrentRequest()) {
            return false;
        }

        if (Request::METHOD_POST !== $this->requestStack->getCurrentRequest()->getMethod()) {
            return false;
        }

        return str_contains($data->getResource(), 'customer_service_records') && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if ([] === $normalizedData) {
            return $normalizedData;
        }

        $normalizedData['discriminator'] = $normalizedData['discriminator'] ?? 'CSR';

        return $normalizedData;
    }
}

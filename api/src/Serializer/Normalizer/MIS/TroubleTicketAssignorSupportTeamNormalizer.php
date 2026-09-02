<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\MIS;

use ApiPlatform\Metadata\Get;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Serializer\Encoder\XlsxEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TroubleTicketAssignorSupportTeamNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'TROUBLE_TICKET_ASSIGNOR_SUPPORT_TEAM_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TroubleTicket && XlsxEncoder::FORMAT !== $format && (false === ($context[self::ALREADY_CALLED] ?? false));
    }

    /**
     * @param TroubleTicket $object
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

        $normalizedData['createdBy']['supportTeam'] = $this->normalizer->normalize($object->createdBy?->getPremise()?->supportTeam?->name, 'jsonld', [
            'groups' => ['location_public'],
        ]) ?? null;

        return $normalizedData;
    }
}

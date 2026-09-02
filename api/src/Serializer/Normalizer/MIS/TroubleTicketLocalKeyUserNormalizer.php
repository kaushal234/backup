<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\MIS;

use ApiPlatform\Metadata\Get;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Serializer\Encoder\XlsxEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TroubleTicketLocalKeyUserNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'TROUBLE_TICKET_LOCAL_KEY_USER_NORMALIZER_ALREADY_CALLED';

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

        $localKeyUser = null;
        foreach ($object->module->getLocalKeyUsers() as $keyUser) {
            if ($keyUser->getBusinessUnit()->getRegion() !== $object->createdBy->getBusinessUnit()->getRegion()) {
                continue;
            }

            $localKeyUser = $keyUser;
        }

        if (null === $localKeyUser) {
            $normalizedData['module']['localKeyUser'] = null;

            return $normalizedData;
        }

        $normalizedData['module']['localKeyUser'] = $this->normalizer->normalize($localKeyUser, 'jsonld', [
            'groups' => ['people_public', 'people_photo', 'file:light'],
        ]);

        return $normalizedData;
    }
}

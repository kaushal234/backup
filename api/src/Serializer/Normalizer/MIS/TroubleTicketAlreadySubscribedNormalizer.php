<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\MIS;

use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\User;
use App\Repository\Common\SubscriptionRepository;
use App\Serializer\Encoder\XlsxEncoder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TroubleTicketAlreadySubscribedNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    public const NORMALIZATION_GROUP_NAME = 'subscription';

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'SUBSCRIPTION_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly Security $security,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TroubleTicket && XlsxEncoder::FORMAT !== $format && \in_array(self::NORMALIZATION_GROUP_NAME, $context[AbstractNormalizer::GROUPS] ?? [], true) && (false === ($context[self::ALREADY_CALLED] ?? false));
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

        /** @var User $user */
        $user = $this->security->getUser();

        $normalizedData['alreadySubscribed'] = $this->subscriptionRepository->isFollowingResource($user, $object);

        return $normalizedData;
    }
}

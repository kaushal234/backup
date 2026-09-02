<?php

declare(strict_types=1);

namespace App\Security\JWT\PayloadGenerator;

use App\Entity\User;
use App\Manager\UserManager;
use App\Serializer\Normalizer\UserNormalizer;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserPayloadGenerator implements PayloadGeneratorInterface
{
    private readonly NormalizerInterface $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function generate(array &$payload, ?UserInterface $user = null): void
    {
        if (!$user instanceof User) {
            return;
        }

        $payload[UserManager::LOGIN_PORTAL] = UserManager::getPortal($user);

        $payload = array_merge(
            $payload,
            $this->normalizer->normalize($user, 'jsonld', [
                AbstractNormalizer::GROUPS => [PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, 'expose_legacy', 'file:light'],
                'jsonld_has_context' => false,
                UserNormalizer::JWT_PAYLOAD => $payload,
            ]
            ));
    }
}

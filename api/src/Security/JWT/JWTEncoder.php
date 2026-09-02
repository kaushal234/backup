<?php

declare(strict_types=1);

namespace App\Security\JWT;

use App\Security\JWT\PayloadGenerator\ChainPayloadGenerator;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\SecurityBundle\Security;

class JWTEncoder implements JWTEncoderInterface
{
    final public const PAYLOAD_USER_KEY = 'user_payload';
    private readonly JWTEncoderInterface $decoratedEncoder;
    private readonly ChainPayloadGenerator $payloadGenerator;
    private readonly Security $security;

    public function __construct(JWTEncoderInterface $decoratedEncoder, ChainPayloadGenerator $payloadGenerator, Security $security)
    {
        $this->decoratedEncoder = $decoratedEncoder;
        $this->payloadGenerator = $payloadGenerator;
        $this->security = $security;
    }

    public function encode(array $data): string
    {
        $user = $data[self::PAYLOAD_USER_KEY] ?? $this->security->getUser();
        unset($data[self::PAYLOAD_USER_KEY]);

        $this->payloadGenerator->generate($data, $user);

        return $this->decoratedEncoder->encode($data);
    }

    public function decode($token): array
    {
        return $this->decoratedEncoder->decode($token);
    }
}

<?php

declare(strict_types=1);

namespace App\Security\JWT\PayloadGenerator;

use Symfony\Component\Security\Core\User\UserInterface;

class ChainPayloadGenerator implements PayloadGeneratorInterface
{
    /**
     * @var iterable|PayloadGeneratorInterface[]
     */
    private readonly iterable $payloadGenerators;

    public function __construct(iterable $payloadGenerators)
    {
        $this->payloadGenerators = $payloadGenerators;
    }

    public function generate(array &$payload, ?UserInterface $user = null): void
    {
        foreach ($this->payloadGenerators as $payloadGenerator) {
            if ($payloadGenerator instanceof self) {
                continue;
            }
            $payloadGenerator->generate($payload, $user);
        }
    }
}

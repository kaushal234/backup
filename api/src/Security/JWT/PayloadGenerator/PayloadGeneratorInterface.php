<?php

declare(strict_types=1);

namespace App\Security\JWT\PayloadGenerator;

use Symfony\Component\Security\Core\User\UserInterface;

interface PayloadGeneratorInterface
{
    public const PAYLOAD_NORMALIZATION_GROUP = 'user:jwt';

    public function generate(array &$payload, ?UserInterface $user = null): void;
}

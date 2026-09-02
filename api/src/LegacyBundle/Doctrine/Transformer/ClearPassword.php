<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

use App\Entity\User;
use LegacyBundle\Exception\NotTransformablePropertyException;

class ClearPassword
{
    public function __invoke($encodedPassword, array $options, User $user)
    {
        if (null === $user->getClearPassword()) {
            throw new NotTransformablePropertyException();
        }

        return $user->getClearPassword();
    }
}

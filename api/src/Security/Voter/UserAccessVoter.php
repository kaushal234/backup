<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Doctrine\Inflector\InflectorFactory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserAccessVoter extends RoleVoter
{
    final public const PREFIX = 'ACCESS_';

    public function __construct()
    {
        parent::__construct(self::PREFIX);
    }

    protected function extractRoles(TokenInterface $token): array
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return [];
        }

        $path = explode('\\', $user::class);
        $classname = array_pop($path);
        $inflector = InflectorFactory::create()->build();

        return [self::PREFIX.mb_strtoupper($inflector->tableize($classname))];
    }
}

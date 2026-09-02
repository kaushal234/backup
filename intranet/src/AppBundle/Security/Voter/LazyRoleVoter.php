<?php

declare(strict_types=1);

namespace AppBundle\Security\Voter;

use ApiBundle\Model\User;
use AppBundle\Security\RoleProvider\RoleProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class LazyRoleVoter extends RoleVoter
{
    protected $prefix;

    private readonly RoleProviderInterface $roleProvider;

    public function __construct(RoleProviderInterface $roleProvider, $prefix = 'ROLE_')
    {
        parent::__construct($prefix);

        $this->prefix = $prefix;

        $this->roleProvider = $roleProvider;
    }

    /**
     * Improve the parent vote by checking if, at least, one attribute is supported.
     *
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes, ?Vote $vote = null): int
    {
        $supported = false;
        foreach ($attributes as $attribute) {
            if (0 !== mb_strpos((string) $attribute, (string) $this->prefix)) {
                continue;
            }

            $supported = true;
            break;
        }

        if (!$supported) {
            return self::ACCESS_ABSTAIN;
        }

        return parent::vote($token, $subject, $attributes);
    }

    protected function extractRoles(TokenInterface $token): array
    {
        if (!($user = $token->getUser()) instanceof User) {
            return [];
        }

        return $this->roleProvider->loadRolesByUser($user);
    }
}

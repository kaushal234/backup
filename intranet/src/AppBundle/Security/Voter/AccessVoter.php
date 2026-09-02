<?php

declare(strict_types=1);

namespace AppBundle\Security\Voter;

use ApiBundle\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccessVoter extends Voter
{
    final public const ATTRIBUTE = 'INTRANET_ACCESS';

    private readonly AccessDecisionManagerInterface $accessDecisionManager;

    public function __construct(AccessDecisionManagerInterface $accessDecisionManager)
    {
        $this->accessDecisionManager = $accessDecisionManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return static::ATTRIBUTE === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($this->accessDecisionManager->decide($token, [User::ROLE_IMPERSONATED])) {
            return true;
        }

        return $this->accessDecisionManager->decide($token, [User::ROLE_PASSWORD_NOT_EXPIRED]) && $this->accessDecisionManager->decide($token, ['ACL_ACL_AUTH_INTRANET']);
    }
}

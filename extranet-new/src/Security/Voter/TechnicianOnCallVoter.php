<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Sdk\Resource\Customer;
use App\Security\User\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class TechnicianOnCallVoter extends Voter
{
    public const CREATE = 'CREATE_TECHNICIAN_ON_CALL';

    public const COMMENT = 'COMMENT_TECHNICIAN_ON_CALL';

    private const ROLE_TOC = 'role_TOC';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::CREATE === $attribute || self::COMMENT === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $activeCustomer = $this->requestStack->getSession()->get('customer');
        if (!$activeCustomer instanceof Customer) {
            return false;
        }

        foreach ($user->acls as $acl) {
            if (self::ROLE_TOC === $acl->group->name) {
                return true;
            }
        }

        return false;
    }
}

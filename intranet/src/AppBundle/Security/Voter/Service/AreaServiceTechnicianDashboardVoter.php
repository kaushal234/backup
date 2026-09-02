<?php

declare(strict_types=1);

namespace AppBundle\Security\Voter\Service;

use ApiBundle\Client;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AreaServiceTechnicianDashboardVoter extends Voter
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return 'AST_DASHBOARD' === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $this->client->get('me');

        return \in_array($user['position']['code'], ['CSM', 'CSTL', 'CSS', 'DSS', 'AST'], true);
    }
}

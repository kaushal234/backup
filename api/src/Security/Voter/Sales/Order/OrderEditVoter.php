<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\Order;

use App\Entity\Directory\People;
use App\Entity\Sales\Order;
use App\Manager\Directory\PeopleManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrderEditVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'SALES_ORDER_EDIT_VOTER' === $attribute && $subject instanceof Order;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$subject instanceof Order || Order::PENDING !== $subject->getStatus()) {
            return false;
        }
        $user = $token->getUser();
        if (!$user instanceof People || $user !== $subject->getAsm()) {
            return false;
        }

        return PeopleManager::hasGroup($user, 'ROLE_ASM');
    }
}

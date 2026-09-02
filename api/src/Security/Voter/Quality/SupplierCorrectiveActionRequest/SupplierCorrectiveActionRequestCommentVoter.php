<?php

declare(strict_types=1);

namespace App\Security\Voter\Quality\SupplierCorrectiveActionRequest;

use App\Entity\Directory\People;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Repository\Common\SubscriptionRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class SupplierCorrectiveActionRequestCommentVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[SubscriptionRepository::class]];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'SUPPLIER_CORRECTIVE_ACTION_REQUEST_COMMENT_VOTER' === $attribute && $subject instanceof SupplierCorrectiveActionRequest;
    }

    /**
     * @param SupplierCorrectiveActionRequest $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        if ('PENDING' === $subject->getStatus()) {
            return false;
        }

        $security = $this->getSecurity();
        $subscribers = $this->serviceLocator->get(SubscriptionRepository::class)->findByResource($subject);

        return $security->isGranted('FEATURE_SCAR_COMMENT') || \in_array($user, $subscribers, true) || $user === $subject->poster;
    }
}

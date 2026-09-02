<?php

declare(strict_types=1);

namespace App\Security\Voter\Finance;

use App\Entity\Directory\People;
use App\Entity\Finance\AccountReceivable;
use App\Entity\Finance\InvoiceRecord;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class AccountReceivableViewVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'ACCOUNT_RECEIVABLES_VIEW_VOTER' === $attribute && ($subject instanceof AccountReceivable || $subject instanceof InvoiceRecord);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        $security = $this->getSecurity();
        if ($security->isGranted('FEATURE_ACCOUNT_RECEIVABLES_VIEW_FULL') || $security->isGranted('MOO_AR')) {
            return true;
        }

        /* @var AccountReceivable|InvoiceRecord $subject */
        return $security->isGranted('FEATURE_ACCOUNT_RECEIVABLES_VIEW_SSO_'.$subject->customerErpReference->getSso()->getId());
    }
}

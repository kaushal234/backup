<?php

declare(strict_types=1);

namespace App\Security\Voter\Finance;

use App\Entity\Directory\People;
use App\Entity\Finance\InvoiceRecord;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class InvoiceRecordWriteVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'INVOICE_RECORD_WRITE_VOTER' === $attribute && $subject instanceof InvoiceRecord;
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

        return
            $security->isGranted('FEATURE_INVOICE_RECORD_WRITE_FULL')
            || $security->isGranted('MOO_AR')
            || $security->isGranted('FEATURE_INVOICE_RECORD_WRITE_SSO_'.$subject->customerErpReference->getSso()->getId())
        ;
    }
}

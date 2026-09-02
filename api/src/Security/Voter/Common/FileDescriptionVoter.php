<?php

declare(strict_types=1);

namespace App\Security\Voter\Common;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualificationFile;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerFile;
use App\Entity\Sales\OrderFile;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class FileDescriptionVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'DESCRIPTION_FILE_VOTER' === $attribute && ($subject instanceof CustomerFile || $subject instanceof Customer || $subject instanceof OrderFile || $subject instanceof FirstArticleQualificationFile);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        switch (true) {
            case $subject instanceof CustomerFile:
                return $this->getSecurity()->isGranted('CUSTOMER_FILES_UPLOAD_VOTER', $subject->getCustomer());
            case $subject instanceof Customer:
                return $this->getSecurity()->isGranted('CUSTOMER_FILES_UPLOAD_VOTER', $subject);
            case $subject instanceof OrderFile:
                return $this->getSecurity()->isGranted('FEATURE_SALES_ORDER_CREATE');
            case $subject instanceof FirstArticleQualificationFile:
                return $this->getSecurity()->isGranted('FAQ_DELETE_FILE_VOTER', $subject->getFirstArticleQualification());
            default:
                return false;
        }
    }
}

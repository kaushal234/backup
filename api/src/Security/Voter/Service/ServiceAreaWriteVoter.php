<?php

declare(strict_types=1);

namespace App\Security\Voter\Service;

use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ServiceAreaWriteVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'SERVICE_AREA_WRITE_VOTER' === $attribute;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $security = $this->getSecurity();

        return $security->isGranted('MOO_TOC')
            || $security->isGranted('MOO_SB3')
            || $security->isGranted('MOO_CSR')
        ;
    }
}

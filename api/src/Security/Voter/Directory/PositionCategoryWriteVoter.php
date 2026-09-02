<?php

declare(strict_types=1);

namespace App\Security\Voter\Directory;

use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class PositionCategoryWriteVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'POSITION_CATEGORY_WRITE_VOTER' === $attribute;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $this->getSecurity()->isGranted('FEATURE_POSITION_CATEGORY_WRITE') || $this->getSecurity()->isGranted('MOO_ESM');
    }
}

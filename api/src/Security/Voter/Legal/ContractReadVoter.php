<?php

declare(strict_types=1);

namespace App\Security\Voter\Legal;

use App\Entity\Directory\People;
use App\Entity\Legal\Contract;
use App\Repository\Legal\ContractRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ContractReadVoter extends AbstractVoter
{
    public const string CONTRACT_READ_VOTER = 'CONTRACT_READ_VOTER';

    public static function getSubscribedServices(): array
    {
        return [
            ...parent::getSubscribedServices(),
            ContractRepository::class,
        ];
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::CONTRACT_READ_VOTER === $attribute && $subject instanceof Contract;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof People) {
            return false;
        }

        $security = $this->getSecurity();
        if ($security->isGranted('FEATURE_FULL_CONTRACT_ACCESS')
            || $security->isGranted('MKU_CRS')
            || $security->isGranted('MOO_CRS')
        ) {
            return true;
        }

        return $this->serviceLocator
            ->get(ContractRepository::class)
            ->isReadableBy($subject, $user);
    }
}

<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Repository\Module\ModuleRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ModuleUserVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [
            ModuleRepository::class,
        ];
    }

    /**
     * {@inheritdoc}.
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 0 === mb_strpos($attribute, 'MOO_') || 0 === mb_strpos($attribute, 'MKU_') || 0 === mb_strpos($attribute, 'LKU_');
    }

    /**
     * {@inheritdoc}.
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $acronym = mb_substr($attribute, 4);

        if (null === $module = $this->serviceLocator->get(ModuleRepository::class)->findByName($acronym)) {
            return false;
        }

        if (0 === mb_strpos($attribute, 'LKU_')) {
            return $module->getLocalKeyUsers()->contains($token->getUser());
        }
        if (0 === mb_strpos($attribute, 'MKU_')) {
            return $token->getUser() === $module->getKeyUser();
        }

        return $token->getUser() === $module->getOperationalOwner();
    }
}

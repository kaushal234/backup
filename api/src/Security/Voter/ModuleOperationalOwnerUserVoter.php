<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Repository\Module\ModuleRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ModuleOperationalOwnerUserVoter extends AbstractVoter
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
        return 'MOO' === $attribute;
    }

    /**
     * {@inheritdoc}.
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return \count($this->serviceLocator->get(ModuleRepository::class)->findBy(['operationalOwner' => $token->getUser()])) > 0;
    }
}

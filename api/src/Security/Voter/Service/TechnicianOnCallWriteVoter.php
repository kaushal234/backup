<?php

declare(strict_types=1);

namespace App\Security\Voter\Service;

use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Repository\Sales\ExtranetUserAclRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class TechnicianOnCallWriteVoter extends AbstractVoter
{
    public const ATTRIBUTE = 'TOC_WRITE_VOTER';

    private const ROLE_TOC = 'role_TOC';

    public static function getSubscribedServices(): array
    {
        return [ExtranetUserAclRepository::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return self::ATTRIBUTE === $attribute;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if ($user instanceof People) {
            return true;
        }
        if (!$user instanceof ExtranetUser) {
            return false;
        }

        return $this->serviceLocator->get(ExtranetUserAclRepository::class)->userHasGroup($user, self::ROLE_TOC);
    }
}

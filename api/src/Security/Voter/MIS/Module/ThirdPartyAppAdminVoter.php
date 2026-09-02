<?php

declare(strict_types=1);

namespace App\Security\Voter\MIS\Module;

use App\Dto\MIS\Module\UserListThirdPartyApp;
use App\Entity\Directory\People;
use App\Entity\Module\ThirdPartyApp\Member;
use App\Entity\Module\ThirdPartyApp\Type\Light;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ThirdPartyAppAdminVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[EntityManagerInterface::class]];
    }

    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_THIRD_PARTY_APP_ADMIN_VOTER' === $attribute;
    }

    /**
     * Check if the user is admin of the third party app.
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $security = $this->getSecurity();

        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        if ($subject instanceof UserListThirdPartyApp) {
            $subject = $subject->module;
        }

        if ($subject instanceof Request && $subject->attributes->has('thirdPartyAppId')) {
            $subject = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(Light::class)->find($subject->attributes->get('thirdPartyAppId'));
        }

        if (!$subject instanceof Light && !$subject instanceof Member) {
            return $security->isGranted('FEATURE_MODULE_WRITE');
        }

        if ($subject instanceof Member) {
            $subject = $subject->getThirdPartyApp();
        }

        if ($subject->getMainAdmin() === $user
            || $subject->getOperationalOwner() === $user) {
            return true;
        }

        $memberRepository = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(Member::class);
        $member = $memberRepository->findOneBy([
            'thirdPartyApp' => $subject,
            'user' => $user,
            'admin' => true,
        ]);

        return $member instanceof Member
            || $security->isGranted('FEATURE_MODULE_WRITE');
    }
}

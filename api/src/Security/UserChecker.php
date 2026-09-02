<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\AuthorizedApplication;
use App\Entity\Purchasing\VendorUser;
use App\Entity\User;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerContactManager;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContactCategory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    private readonly BusinessPartnerContactManager $businessPartnerContactManager;

    public function __construct(BusinessPartnerContactManager $businessPartnerContactManager)
    {
        $this->businessPartnerContactManager = $businessPartnerContactManager;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof User && !$user->isEnabled()) {
            $this->throwDisabledUserException('User account is disabled.', $user);
        }

        if ($user instanceof AuthorizedApplication && $user->disabled) {
            $this->throwDisabledUserException(\sprintf('The authorized application "%s" is disabled.', $user->name), $user);
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if ($user instanceof VendorUser) {
            $contact = $user->contact ?? $this->businessPartnerContactManager->findByErpIdentifier($user->getErpIdentifier());

            if (!$contact instanceof BusinessPartnerContact || !$contact->isGrantedCategory(BusinessPartnerContactCategory::REQUIRED_CATEGORY_NAME)) {
                $this->throwDisabledUserException('The user does not have the evendors category in LN.', $user);
            }
        }
    }

    private function throwDisabledUserException(string $message, UserInterface $user): never
    {
        $exception = new DisabledException($message);
        $exception->setUser($user);
        throw $exception;
    }
}

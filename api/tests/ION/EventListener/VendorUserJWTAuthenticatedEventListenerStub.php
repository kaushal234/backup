<?php

declare(strict_types=1);

namespace App\Tests\ION\EventListener;

use App\Entity\Purchasing\VendorUser;
use App\ION\EventListener\VendorUserJWTAuthenticatedEventListener;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContactCategory;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;

class VendorUserJWTAuthenticatedEventListenerStub extends VendorUserJWTAuthenticatedEventListener
{
    public function onJWTAuthenticated(JWTAuthenticatedEvent $event)
    {
        parent::onJWTAuthenticated($event);

        $user = $event->getToken()->getUser();

        if (!$user instanceof VendorUser) {
            return;
        }

        $user->contact = $user->contact ?? new BusinessPartnerContact();
        $category = new BusinessPartnerContactCategory();
        $category->code = BusinessPartnerContactCategory::REQUIRED_CATEGORY_NAME;
        $user->contact->addCategory($category);

        $businessPartner = new BusinessPartner();
        $businessPartner->code = '11599';
        $businessPartner->name = 'Business Partner for tests';

        $baanSupplier = new BusinessPartner();
        $baanSupplier->code = 'TA2500';
        $baanSupplier->name = 'ALOT METAL S.L.';

        $user->contact
            ->addBusinessPartner($businessPartner)
            ->addBusinessPartner($baanSupplier)
        ;
    }
}

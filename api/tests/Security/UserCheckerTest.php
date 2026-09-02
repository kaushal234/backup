<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Sales\ExtranetUser;
use App\Entity\User;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerContactManager;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContactCategory;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Security\Core\Exception\DisabledException;

class UserCheckerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @doesNotPerformAssertions
     */
    public function testCheckPreAuth()
    {
        $user = new User();
        $user->setDisabled(false);

        (new UserChecker($this->prophesize(BusinessPartnerContactManager::class)->reveal()))->checkPreAuth($user);
    }

    public function testCheckPreAuthThrows()
    {
        $this->expectException(DisabledException::class);
        $this->expectExceptionMessage('User account is disabled.');

        (new UserChecker($this->prophesize(BusinessPartnerContactManager::class)->reveal()))->checkPreAuth(new User());
    }

    public function testCheckPostAuthVerifyVendorUsersAndThrows()
    {
        $this->expectException(DisabledException::class);

        $user = new VendorUser();
        $user->setErpIdentifier('DA471');
        $contact = new BusinessPartnerContact();
        $category = new BusinessPartnerContactCategory();
        $category->code = 'not the correct category';
        $contact->addCategory($category);
        $businessPartnerContactManagerProphecy = $this->prophesize(BusinessPartnerContactManager::class);
        $businessPartnerContactManagerProphecy->findByErpIdentifier('DA471')->shouldBeCalledOnce()->willReturn($contact);
        (new UserChecker($businessPartnerContactManagerProphecy->reveal()))->checkPostAuth($user);
    }

    public function testCheckPostAuthVerifyVendorUsers()
    {
        $user = new VendorUser();
        $user->setErpIdentifier('DA471');
        $businessPartnerContactManagerProphecy = $this->prophesize(BusinessPartnerContactManager::class);
        $businessPartnerContactManagerProphecy->findByErpIdentifier('DA471')->shouldBeCalledOnce()->willReturn($this->getValidContact());
        (new UserChecker($businessPartnerContactManagerProphecy->reveal()))->checkPostAuth($user);
    }

    public function testCheckPostAuthSkipAlreadyVerifiedVendorUsers()
    {
        $user = new VendorUser();
        $user->setErpIdentifier('DA471');
        $user->contact = $this->getValidContact();
        $businessPartnerContactManagerProphecy = $this->prophesize(BusinessPartnerContactManager::class);
        $businessPartnerContactManagerProphecy->findByErpIdentifier(Argument::any())->shouldNotBeCalled();
        (new UserChecker($businessPartnerContactManagerProphecy->reveal()))->checkPostAuth($user);
    }

    public function testCheckPostAuthDoNotCheckOtherUsers()
    {
        $user = new People();
        $user->setErpIdentifier('DA471');
        $businessPartnerContactManagerProphecy = $this->prophesize(BusinessPartnerContactManager::class);
        $businessPartnerContactManagerProphecy->findByErpIdentifier(Argument::any())->shouldNotBeCalled();
        (new UserChecker($businessPartnerContactManagerProphecy->reveal()))->checkPostAuth($user);

        $user = new ExtranetUser();
        $user->setErpIdentifier('DA471');
        $businessPartnerContactManagerProphecy = $this->prophesize(BusinessPartnerContactManager::class);
        $businessPartnerContactManagerProphecy->findByErpIdentifier(Argument::any())->shouldNotBeCalled();
        (new UserChecker($businessPartnerContactManagerProphecy->reveal()))->checkPostAuth($user);
    }

    private function getValidContact(): BusinessPartnerContact
    {
        $contact = new BusinessPartnerContact();
        $category = new BusinessPartnerContactCategory();
        $category->code = BusinessPartnerContactCategory::REQUIRED_CATEGORY_NAME;
        $contact->addCategory($category);

        return $contact;
    }
}

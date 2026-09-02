<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints\Service\TechnicianOnCall;

use App\Entity\Common\Airport;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Entity\Sales\ExtranetUserProfile;
use App\Entity\Service\ServiceActivity;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallType;
use Doctrine\Common\Collections\ArrayCollection;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TechnicianOnCallGroupSequenceValidationTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testDefaultValidationRaisedWithoutContactsViolations()
    {
        $technicianOnCall = new TechnicianOnCall();

        $mainContact = $this->prophesize(ExtranetUser::class);
        $mainContact->isDisabled()->willReturn(true);
        $mainContact->getUsername()->willReturn('disabled_user');

        $equipmentRecord = $this->prophesize(EquipmentRecord::class);
        $equipmentRecord->getSerialNumber()->willReturn('T98712');

        $technicianOnCall->setMainContact($mainContact->reveal());

        $violations = $this->getContainer()->get('validator')->validate($technicianOnCall);

        foreach ($violations as $violation) {
            if (empty($violation->getPropertyPath())) {
                continue;
            }
            self::assertStringNotContainsString($violation->getPropertyPath(), 'contacts');
            self::assertStringNotContainsString($violation->getPropertyPath(), 'mainContacts');
        }
    }

    public function testContactViolationRaisedWhenDefaultAssertsAreValid()
    {
        $technicianOnCall = new TechnicianOnCall();

        $extranetUserProfile = new ExtranetUserProfile();
        $extranetUserProfile->archived = true;

        $mainContact = $this->prophesize(ExtranetUser::class);
        $mainContact->isDisabled()->willReturn(true);
        $mainContact->getUsername()->willReturn('disabled_user');
        $mainContact->getExtranetUserProfile()->willReturn($extranetUserProfile);

        $crt = $this->prophesize(CustomerRelationshipTeam::class);
        $acl = $this->prophesize(ExtranetUserAcl::class);

        $mainContact->getExtranetUserAcls()->willReturn(new ArrayCollection([$acl->reveal()]));
        $acl->getCrt()->willReturn($crt->reveal());

        $customer = $this->prophesize(Customer::class);
        $customer->getCrt()->willReturn(new ArrayCollection([$crt->reveal()]));

        $equipmentRecord = $this->prophesize(EquipmentRecord::class);
        $equipmentRecord->getSerialNumber()->willReturn('T98712');
        $equipmentRecord->getState()->willReturn('ACTIVE');
        $equipmentRecord->getDateShipped()->willReturn(new \DateTimeImmutable('-6 month'));

        $equipmentRecord->getBuyer()->willReturn($customer->reveal());
        $equipmentRecord->getEndUser()->willReturn(null);
        $equipmentRecord->getMaintainer()->willReturn(null);

        $airport = $this->prophesize(Airport::class);
        $ssoService = $this->prophesize(Location::class);
        $serviceActivity = $this->prophesize(ServiceActivity::class);
        $tocType = $this->prophesize(TechnicianOnCallType::class);

        $technicianOnCall->equipmentRecord = $equipmentRecord->reveal();
        $technicianOnCall->title = 'TestToc title';
        $technicianOnCall->originalTitle = 'TestToc title';
        $technicianOnCall->description = 'TestToc description';
        $technicianOnCall->originalDescription = 'TestToc description';
        $technicianOnCall->setMainContact($mainContact->reveal());
        $revealedServiceActivity = $serviceActivity->reveal();
        $revealedServiceActivity->name = 'Troubleshooting';
        $technicianOnCall->serviceActivity = $revealedServiceActivity;
        $technicianOnCall->technicianOnCallType = $tocType->reveal();
        $technicianOnCall->airport = $airport->reveal();
        $technicianOnCall->salesOrganisationService = $ssoService->reveal();
        $technicianOnCall->customer = new Customer();

        $violations = $this->getContainer()->get('validator')->validate($technicianOnCall);

        $this->assertStringContainsString('disabled_user is disabled. Cannot be added as Contact', $violations->get(0)->getMessage());
    }
}

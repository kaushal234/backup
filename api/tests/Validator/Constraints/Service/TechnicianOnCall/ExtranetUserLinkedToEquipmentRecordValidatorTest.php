<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints\Service\TechnicianOnCall;

use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Entity\Sales\ExtranetUserProfile;
use App\Entity\Service\TechnicianOnCall;
use App\Validator\Constraints\Service\TechnicianOnCall\ExtranetUserLinkedToEquipmentRecord;
use App\Validator\Constraints\Service\TechnicianOnCall\ExtranetUserLinkedToEquipmentRecordValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ExtranetUserLinkedToEquipmentRecordValidatorTest extends ConstraintValidatorTestCase
{
    public function testNullValue(): void
    {
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->equipmentRecord = new EquipmentRecord();
        $this->setObject($technicianOnCall);

        $this->validator->validate(null, new ExtranetUserLinkedToEquipmentRecord());

        $this->assertNoViolation();
    }

    public function testContactNotAllowed(): void
    {
        $extranetUserProfile = new ExtranetUserProfile();

        $extranetUser = new ExtranetUser();
        $extranetUser->setDisabled(false);
        $extranetUser->setHidden(false);
        $extranetUser->setUsername('NotLinked');
        $extranetUser->setExtranetUserProfile($extranetUserProfile);

        $crtContact = new CustomerRelationshipTeam();
        $extranetUserAcl = new ExtranetUserAcl();
        $extranetUserAcl->setCrt($crtContact);
        $extranetUser->addExtranetUserAcl($extranetUserAcl);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->setMainContact($extranetUser);

        $customer = new Customer();
        $crtCustomer = new CustomerRelationshipTeam();
        $customer->getCrt()->add($crtCustomer);
        $technicianOnCall->customer = $customer;

        $this->setObject($technicianOnCall);

        $this->validator->validate($extranetUser, new ExtranetUserLinkedToEquipmentRecord());

        $this->buildViolation('toc.messages.errors.contact_not_allowed')
            ->setTranslationDomain('technician_on_call')
            ->setParameter('%extranetUser%', 'NotLinked')
            ->assertRaised();
    }

    public function testNoViolation(): void
    {
        $extranetUserProfile = new ExtranetUserProfile();

        $extranetUser = new ExtranetUser();
        $extranetUser->setDisabled(false);
        $extranetUser->setUsername('ContactOk');
        $extranetUser->setExtranetUserProfile($extranetUserProfile);

        $crt = new CustomerRelationshipTeam();
        $extranetUserAcl = new ExtranetUserAcl();
        $extranetUserAcl->setCrt($crt);
        $extranetUser->addExtranetUserAcl($extranetUserAcl);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->setMainContact($extranetUser);

        $customer = new Customer();
        $customer->getCrt()->add($crt);
        $technicianOnCall->customer = $customer;

        $this->setObject($technicianOnCall);

        $this->validator->validate($extranetUser, new ExtranetUserLinkedToEquipmentRecord());

        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new ExtranetUserLinkedToEquipmentRecordValidator();
    }
}

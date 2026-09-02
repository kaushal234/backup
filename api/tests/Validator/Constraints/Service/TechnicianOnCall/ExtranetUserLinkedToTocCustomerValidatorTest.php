<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints\Service\TechnicianOnCall;

use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Entity\Sales\ExtranetUserProfile;
use App\Entity\Service\TechnicianOnCall;
use App\Validator\Constraints\Service\TechnicianOnCall\ExtranetUserLinkedToTocCustomer;
use App\Validator\Constraints\Service\TechnicianOnCall\ExtranetUserLinkedToTocCustomerValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ExtranetUserLinkedToTocCustomerValidatorTest extends ConstraintValidatorTestCase
{
    public function testNullValue(): void
    {
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->customer = new Customer();
        $this->setObject($technicianOnCall);

        $this->validator->validate(null, new ExtranetUserLinkedToTocCustomer());

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

        $customer = new Customer();
        $customer->getCrt()->add(new CustomerRelationshipTeam());

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->customer = $customer;
        $this->setObject($technicianOnCall);

        $this->validator->validate($extranetUser, new ExtranetUserLinkedToTocCustomer());

        $this->buildViolation('toc.messages.errors.main_contact_not_allowed')
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

        $customer = new Customer();
        $customer->getCrt()->add($crt);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->customer = $customer;
        $this->setObject($technicianOnCall);

        $this->validator->validate($extranetUser, new ExtranetUserLinkedToTocCustomer());

        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new ExtranetUserLinkedToTocCustomerValidator();
    }
}

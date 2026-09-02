<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints\Service\TechnicianOnCall;

use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use App\Entity\Service\NestedTechnicianOnCallCustomerServiceRecord;
use App\Entity\Service\TechnicianOnCall;
use App\Validator\Constraints\Service\TechnicianOnCall\CustomerServiceRecordExist;
use App\Validator\Constraints\Service\TechnicianOnCall\CustomerServiceRecordExistValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CustomerServiceRecordExistValidatorTest extends ConstraintValidatorTestCase
{
    /** @dataProvider provider */
    public function testNoViolation(Collection $collection, TechnicianOnCall $technicianOnCall): void
    {
        $this->object = $technicianOnCall;
        $this->context->setNode($collection, $technicianOnCall, $this->metadata, $this->propertyPath);
        $this->validator->validate($collection, new CustomerServiceRecordExist());

        $this->assertNoViolation();
    }

    public function provider()
    {
        yield 'Null CSR and nestedCSR' => [new ArrayCollection(), new TechnicianOnCall()];

        $technicianOnCall = new TechnicianOnCall();
        $nestedCSR = new NestedTechnicianOnCallCustomerServiceRecord();
        $technicianOnCall->nestedCustomerServiceRecord = $nestedCSR;
        yield 'Null CSR and existing Nested CSR' => [new ArrayCollection(), $technicianOnCall];

        $csr = new TechnicianOnCallCustomerServiceRecord();
        yield 'Existing CSR and null Nested CSR' => [new ArrayCollection([$csr]), new TechnicianOnCall()];
    }

    public function testViolation(): void
    {
        $nestedCSR = new NestedTechnicianOnCallCustomerServiceRecord();
        $csr = new TechnicianOnCallCustomerServiceRecord();

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->nestedCustomerServiceRecord = $nestedCSR;
        $technicianOnCall->addCustomerServiceRecord($csr);

        $this->context->setNode($technicianOnCall->getCustomerServiceRecords(), $technicianOnCall, $this->metadata, $this->propertyPath);
        $this->validator->validate($technicianOnCall->getCustomerServiceRecords(), new CustomerServiceRecordExist());

        $this->buildViolation('technician_on_call.customer_service_record.already_exist')
            ->setInvalidValue($technicianOnCall->getCustomerServiceRecords())
            ->atPath('property.path')
            ->assertRaised()
        ;
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new CustomerServiceRecordExistValidator();
    }
}

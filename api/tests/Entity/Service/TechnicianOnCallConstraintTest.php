<?php

declare(strict_types=1);

namespace App\Tests\Entity\Service;

use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Service\ServiceActivity;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TechnicianOnCallConstraintTest extends KernelTestCase
{
    protected TechnicianOnCall $technicianOnCall;

    protected function setUp(): void
    {
        $this->technicianOnCall = new TechnicianOnCall();
        $this->technicianOnCall->equipmentRecord = new EquipmentRecord();
        $this->technicianOnCall->technicianOnCallType = new TechnicianOnCallType();
        $serviceActivity = new ServiceActivity();
        $serviceActivity->name = 'Troubleshooting';
        $this->technicianOnCall->serviceActivity = $serviceActivity;
        $this->technicianOnCall->customer = new Customer();
        $this->technicianOnCall->title = 'Title';
        $this->technicianOnCall->description = 'Description';
    }

    /** @dataProvider closedStatuses */
    public function testTechnicianOnCallCannotBeSolvedWithoutSymptomRootCauseAndSolution(string $status): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get(ValidatorInterface::class);

        $this->technicianOnCall->status = $status;
        $constraintViolation = $validator->validate($this->technicianOnCall);

        $this->assertCount(7, $constraintViolation);

        self::assertSame('originalTitle', $constraintViolation[0]->getPropertyPath());
        self::assertSame('This value should not be blank.', $constraintViolation[0]->getMessage());
        self::assertSame('originalDescription', $constraintViolation[1]->getPropertyPath());
        self::assertSame('This value should not be blank.', $constraintViolation[1]->getMessage());
        self::assertSame('originalSymptoms', $constraintViolation[2]->getPropertyPath());
        self::assertSame('This value should not be blank.', $constraintViolation[2]->getMessage());
        self::assertSame('originalRootCause', $constraintViolation[3]->getPropertyPath());
        self::assertSame('This value should not be blank.', $constraintViolation[3]->getMessage());
        self::assertSame('originalSolution', $constraintViolation[4]->getPropertyPath());
        self::assertSame('This value should not be blank.', $constraintViolation[4]->getMessage());
        self::assertSame('airport', $constraintViolation[5]->getPropertyPath());
        self::assertSame('This value should not be blank.', $constraintViolation[5]->getMessage());
        self::assertSame('salesOrganisationService', $constraintViolation[6]->getPropertyPath());
        self::assertSame('This value should not be blank.', $constraintViolation[6]->getMessage());
    }

    public function closedStatuses(): \Generator
    {
        foreach (TechnicianOnCall::CLOSED_STATUSES as $status) {
            yield 'Test status '.$status => [$status];
        }
    }
}

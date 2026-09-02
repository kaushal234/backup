<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Validator\Constraints\Service\CompleteIntervention;
use App\Validator\Constraints\Service\CompleteInterventionValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CompleteInterventionValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    public function dataProvider(): \Generator
    {
        yield 'To continue status with completed CSR and date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::COMPLETED,
            'endedAt' => new \DateTime(),
            'expectViolation' => false,
        ];

        yield 'To continue status with completed CSR without date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::COMPLETED,
            'endedAt' => null,
            'expectViolation' => false,
        ];

        yield 'Solved status with completed CSR and date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::COMPLETED,
            'endedAt' => new \DateTime(),
            'expectViolation' => false,
        ];

        yield 'Solved status with completed CSR without date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::COMPLETED,
            'endedAt' => null,
            'expectViolation' => false,
        ];

        yield 'To continue status with closed CSR and date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::CLOSED,
            'endedAt' => new \DateTime(),
            'expectViolation' => false,
        ];

        yield 'To continue status with closed CSR without date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::CLOSED,
            'endedAt' => null,
            'expectViolation' => false,
        ];

        yield 'Solved status with closed CSR and date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::CLOSED,
            'endedAt' => new \DateTime(),
            'expectViolation' => false,
        ];

        yield 'Solved status with closed CSR without date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::CLOSED,
            'endedAt' => null,
            'expectViolation' => false,
        ];

        yield 'Solved status with pending CSR status and date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::PENDING,
            'endedAt' => new \DateTime(),
            'expectViolation' => true,
        ];

        yield 'Solved status with pending CSR status without date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::PENDING,
            'endedAt' => null,
            'expectViolation' => true,
        ];

        yield 'To continue status with pending CSR status and date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::PENDING,
            'endedAt' => new \DateTime(),
            'expectViolation' => true,
        ];

        yield 'To continue status with pending CSR status without date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::PENDING,
            'endedAt' => null,
            'expectViolation' => true,
        ];

        yield 'Solved status with in progress CSR status and date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::IN_PROGRESS,
            'endedAt' => new \DateTime(),
            'expectViolation' => false,
        ];

        yield 'Solved status with in progress CSR status without date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::IN_PROGRESS,
            'endedAt' => null,
            'expectViolation' => false,
        ];

        yield 'To continue status with in progress CSR status and date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::IN_PROGRESS,
            'endedAt' => new \DateTime(),
            'expectViolation' => false,
        ];

        yield 'To continue status with in progress CSR status without date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::IN_PROGRESS,
            'endedAt' => null,
            'expectViolation' => false,
        ];

        yield 'Solved status with assigned CSR status and date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::ASSIGNED,
            'endedAt' => new \DateTime(),
            'expectViolation' => false,
        ];

        yield 'Solved status with assigned CSR status without date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::ASSIGNED,
            'endedAt' => null,
            'expectViolation' => false,
        ];

        yield 'To continue status with assigned CSR status and date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::ASSIGNED,
            'endedAt' => new \DateTime(),
            'expectViolation' => false,
        ];

        yield 'To continue status with assigned CSR status without date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::ASSIGNED,
            'endedAt' => null,
            'expectViolation' => false,
        ];

        yield 'Solved status with planned CSR status and date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::PLANNED,
            'endedAt' => new \DateTime(),
            'expectViolation' => true,
        ];

        yield 'Solved status with planned CSR status without date' => [
            'interventionStatus' => Intervention::SOLVED,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::PLANNED,
            'endedAt' => null,
            'expectViolation' => true,
        ];

        yield 'To continue status with planned CSR status and date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::PLANNED,
            'endedAt' => new \DateTime(),
            'expectViolation' => true,
        ];

        yield 'To continue status with planned CSR status without date' => [
            'interventionStatus' => Intervention::TO_CONTINUE,
            'customerServiceRecordStatus' => AbstractCustomerServiceRecord::PLANNED,
            'endedAt' => null,
            'expectViolation' => true,
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testValidate(string $interventionStatus, string $customerServiceRecordStatus, ?\DateTime $endedAt, bool $expectViolation): void
    {
        $customerServiceRecord = new CustomerServiceRecord();
        $customerServiceRecord->setStatus($customerServiceRecordStatus);
        $intervention = new Intervention();
        $intervention->setStatus($interventionStatus);
        $intervention->customerServiceRecord = $customerServiceRecord;
        $intervention->endedAt = $endedAt;

        $constraint = new CompleteIntervention();
        $this->validator->validate($intervention, $constraint);
        $numberViolation = 0;
        $violation = null;

        if (null === $endedAt) {
            ++$numberViolation;
            $violation = $this->buildViolation($constraint->messages['end_date'])->atPath('property.path.status');
        }

        if ($expectViolation) {
            ++$numberViolation;
            $violation = $this->buildViolation($constraint->messages[$interventionStatus])->atPath('property.path.status');
        }

        if ($numberViolation) {
            self::assertCount($numberViolation, $this->context->getViolations());
        }

        if (1 === $numberViolation) {
            $violation->assertRaised();
        }

        if (null !== $endedAt && !$expectViolation) {
            $this->assertNoViolation();
        }
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        $unitOfWork = $this->prophesize(UnitOfWork::class);
        $unitOfWork->getOriginalEntityData(Argument::any())->willReturn(['status' => null]);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getUnitOfWork()->willReturn($unitOfWork->reveal());

        return new CompleteInterventionValidator($entityManagerProphecy->reveal());
    }
}

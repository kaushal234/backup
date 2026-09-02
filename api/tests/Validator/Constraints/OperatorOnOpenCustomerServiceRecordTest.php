<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Validator\Constraints\Service\OperatorOnOpenCustomerServiceRecords;
use App\Validator\Constraints\Service\OperatorOnOpenCustomerServiceRecordsValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class OperatorOnOpenCustomerServiceRecordTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    public function testNoViolationWithNoOperators(): void
    {
        $intervention = new Intervention();

        $constraint = new OperatorOnOpenCustomerServiceRecords();

        $this->validator->validate($intervention, $constraint);

        $this->assertNoViolation();
    }

    public function testNoViolationWithOpenIntervention(): void
    {
        $intervention = new Intervention();
        $intervention->setStatus('PENDING');

        $reflection = new \ReflectionClass($intervention);
        $operators = $reflection->getProperty('operators');
        $operators->setAccessible(true);

        $persistantCollection = new PersistentCollection(
            $this->prophesize(EntityManagerInterface::class)->reveal(),
            $this->prophesize(ClassMetadata::class)->reveal(),
            new ArrayCollection()
        );

        $operators->setValue($intervention, $persistantCollection);

        $constraint = new OperatorOnOpenCustomerServiceRecords();

        $this->validator->validate($intervention, $constraint);

        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new OperatorOnOpenCustomerServiceRecordsValidator();
    }
}

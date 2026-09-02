<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Validator\Constraints\Service\UniqueOpenedIntervention;
use App\Validator\Constraints\Service\UniqueOpenedInterventionValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UniqueOpenedInterventionValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    public function testValidationSuccess()
    {
        $this->validator->validate(new ArrayCollection(), new UniqueOpenedIntervention());
        $this->assertNoViolation();
    }

    public function testValidationSuccessWithOneOpenIntervention()
    {
        $interventionProphecy = $this->prophesize(Intervention::class);
        $interventionProphecy->isOpen()->shouldBeCalledOnce()->willReturn(true);

        $collection = new ArrayCollection();
        $collection->add($interventionProphecy->reveal());

        $this->validator->validate($collection, new UniqueOpenedIntervention());
        $this->assertNoViolation();
    }

    public function testValidationSuccessWithOneOpenInterventionAndManyInterventions()
    {
        $interventionProphecy = $this->prophesize(Intervention::class);
        $interventionProphecy->isOpen()->shouldBeCalledOnce()->willReturn(false);

        $collection = new ArrayCollection();
        $collection->add($interventionProphecy->reveal());

        $this->validator->validate($collection, new UniqueOpenedIntervention());
        $this->assertNoViolation();
    }

    public function testValidationFailedWithMoreThanOneOpenIntervention()
    {
        $interventionProphecy = $this->prophesize(Intervention::class);
        $interventionProphecy->isOpen()->shouldBeCalledOnce()->willReturn(true);
        $intervention2Prophecy = $this->prophesize(Intervention::class);
        $intervention2Prophecy->isOpen()->shouldBeCalledOnce()->willReturn(true);

        $collection = new ArrayCollection();
        $collection->add($interventionProphecy->reveal());
        $collection->add($intervention2Prophecy->reveal());

        $this->validator->validate($collection, new UniqueOpenedIntervention());
        $this->buildViolation('An inter. already planned for this CSR')->assertRaised();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new UniqueOpenedInterventionValidator();
    }
}

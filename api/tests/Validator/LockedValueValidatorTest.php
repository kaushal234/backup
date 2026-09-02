<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use App\Validator\Constraints\LockedValue;
use App\Validator\Constraints\LockedValueValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class LockedValueValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    private ObjectProphecy $propertyAccessor;
    private ObjectProphecy $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->propertyAccessor = $this->prophesize(PropertyAccessorInterface::class);
        parent::setUp();
    }

    public function testOnValidEntity()
    {
        $object = new \stdClass();

        $originalData = ['name' => 'SUCCESS'];

        $uowProphecy = $this->prophesize(UnitOfWork::class);
        $uowProphecy->getOriginalEntityData($object)->shouldBeCalledTimes(1)->willReturn($originalData);
        $this->entityManager->getUnitOfWork()->shouldBeCalledTimes(1)->willReturn($uowProphecy->reveal());

        $this->propertyAccessor->getValue($object, 'name')->shouldBeCalledTimes(1)->willReturn('SUCCESS');
        $this->propertyAccessor->getValue($originalData, '[name]')->shouldBeCalledTimes(1)->willReturn('SUCCESS');

        $constraint = new LockedValue(value: 'SUCCESS', propertyPath: 'name');

        $this->validator->validate($object, $constraint);

        $this->assertNoViolation();
    }

    public function testOnInvalidEntity()
    {
        $object = new \stdClass();

        $originalData = ['name' => 'SUCCESS'];

        $uowProphecy = $this->prophesize(UnitOfWork::class);
        $uowProphecy->getOriginalEntityData($object)->shouldBeCalledTimes(1)->willReturn($originalData);
        $this->entityManager->getUnitOfWork()->shouldBeCalledTimes(1)->willReturn($uowProphecy->reveal());

        $this->propertyAccessor->getValue($object, 'name')->shouldBeCalledTimes(1)->willReturn('MODIFIED');
        $this->propertyAccessor->getValue($originalData, '[name]')->shouldBeCalledTimes(1)->willReturn('SUCCESS');

        $constraint = new LockedValue(value: 'SUCCESS', propertyPath: 'name');

        $this->validator->validate($object, $constraint);

        $this->buildViolation("Value '{{ value }}' is locked and can't be updated.")
            ->setParameter('{{ value }}', 'SUCCESS')
            ->atPath('property.path.name')
            ->assertRaised();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->entityManager->reveal();
        /** @var PropertyAccessorInterface $propertyAccessor */
        $propertyAccessor = $this->propertyAccessor->reveal();

        return new LockedValueValidator($entityManager, $propertyAccessor);
    }
}

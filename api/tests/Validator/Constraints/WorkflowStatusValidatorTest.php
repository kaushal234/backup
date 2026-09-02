<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Validator\Constraints\WorkflowStatus;
use App\Validator\Constraints\WorkflowStatusValidator;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Workflow\Exception\LogicException;

class WorkflowStatusValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    public ObjectProphecy $entityManagerProphecy;
    public ObjectProphecy $workflowStatusUpdaterProphecy;

    protected function setUp(): void
    {
        $this->entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $this->workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        parent::setUp();
    }

    public function testNoDataOnUnitOfWork()
    {
        $object = new WorkflowObject();

        $unitOfWorkProphecy = $this->prophesize(UnitOfWork::class);
        $unitOfWorkProphecy->getOriginalEntityData($object)->shouldBeCalledOnce()->willReturn([]);

        $this->entityManagerProphecy->getUnitOfWork()->shouldBeCalledOnce()->willReturn($unitOfWorkProphecy->reveal());
        $this->workflowStatusUpdaterProphecy->applyStatus()->shouldNotBeCalled();

        $this->validator->validate($object, new WorkflowStatus());
    }

    public function testNoChange()
    {
        $object = new WorkflowObject();

        $unitOfWorkProphecy = $this->prophesize(UnitOfWork::class);
        $unitOfWorkProphecy->getOriginalEntityData($object)->shouldBeCalledOnce()->willReturn(['status' => 'a']);

        $this->entityManagerProphecy->getUnitOfWork()->shouldBeCalledOnce()->willReturn($unitOfWorkProphecy->reveal());
        $this->workflowStatusUpdaterProphecy->applyStatus()->shouldNotBeCalled();

        $this->validator->validate($object, new WorkflowStatus());
    }

    public function testAllowedStatusChange()
    {
        $object = new WorkflowObject();
        $object->setStatus('b');

        $unitOfWorkProphecy = $this->prophesize(UnitOfWork::class);
        $unitOfWorkProphecy->getOriginalEntityData($object)->shouldBeCalledOnce()->willReturn(['status' => 'a']);

        $this->entityManagerProphecy->getUnitOfWork()->shouldBeCalledOnce()->willReturn($unitOfWorkProphecy->reveal());

        $this->workflowStatusUpdaterProphecy->applyStatus(Argument::any(), 'b')->shouldBeCalledOnce();

        $this->validator->validate($object, new WorkflowStatus());
    }

    public function testNotAllowedStatusChange()
    {
        $object = new WorkflowObject();
        $object->setStatus('b');

        $unitOfWorkProphecy = $this->prophesize(UnitOfWork::class);
        $unitOfWorkProphecy->getOriginalEntityData($object)->shouldBeCalledOnce()->willReturn(['status' => 'a']);

        $this->entityManagerProphecy->getUnitOfWork()->shouldBeCalledOnce()->willReturn($unitOfWorkProphecy->reveal());

        $this->workflowStatusUpdaterProphecy->applyStatus(Argument::any(), 'b')->shouldBeCalledOnce()->willThrow(new LogicException('error message'));

        $this->validator->validate($object, new WorkflowStatus());
        $this->buildViolation('error message')->assertRaised();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        $this->entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $this->workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);

        return new WorkflowStatusValidator(
            $this->entityManagerProphecy->reveal(),
            $this->workflowStatusUpdaterProphecy->reveal()
        );
    }
}

class WorkflowObject
{
    public string $status = 'a';

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }
}

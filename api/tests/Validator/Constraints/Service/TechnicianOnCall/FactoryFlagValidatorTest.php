<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall;
use App\Validator\Constraints\Service\TechnicianOnCall\FactoryFlag;
use App\Validator\Constraints\Service\TechnicianOnCall\FactoryFlagValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class FactoryFlagValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    private ObjectProphecy $entityManagerProphecy;
    private ObjectProphecy $unitOfWorkProphecy;
    private ObjectProphecy $securityProphecy;

    public function testNoOriginalDataRaisedWithoutContactsViolations()
    {
        $this->setObject($technicianOnCall = new TechnicianOnCall());

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn([]);

        $this->validator->validate(true, new FactoryFlag());
        $this->assertNoViolation();
    }

    public function testNoChangeRaisedWithoutContactsViolations()
    {
        $this->setObject($technicianOnCall = new TechnicianOnCall());

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn(['factoryFlag' => true]);

        $this->validator->validate(true, new FactoryFlag());
        $this->assertNoViolation();
    }

    public function testChangeToTrueWithoutAccess()
    {
        $this->setObject($technicianOnCall = new TechnicianOnCall());

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn(['factoryFlag' => false]);
        $this->securityProphecy->isGranted('FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG')->willReturn(false);

        $this->validator->validate(true, new FactoryFlag());

        $this
            ->buildViolation('toc.messages.security.factory_flag_open')
            ->assertRaised()
        ;
    }

    public function testChangeToTrueWithAccess()
    {
        $this->setObject($technicianOnCall = new TechnicianOnCall());

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn(['factoryFlag' => false]);
        $this->securityProphecy->isGranted('FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG')->willReturn(true);

        $this->validator->validate(true, new FactoryFlag());

        $this->assertNoViolation();
    }

    public function testChangeToFalseWithoutAccess()
    {
        $this->setObject($technicianOnCall = new TechnicianOnCall());

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn(['factoryFlag' => true]);
        $this->securityProphecy->isGranted('FEATURE_TECHNICIAN_ON_CALL_CLOSE_FACTORY_FLAG')->willReturn(false);

        $this->validator->validate(false, new FactoryFlag());

        $this
            ->buildViolation('toc.messages.security.factory_flag_close')
            ->assertRaised()
        ;
    }

    public function testChangeToFalseWithAccess()
    {
        $this->setObject($technicianOnCall = new TechnicianOnCall());

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn(['factoryFlag' => true]);
        $this->securityProphecy->isGranted('FEATURE_TECHNICIAN_ON_CALL_CLOSE_FACTORY_FLAG')->willReturn(true);

        $this->validator->validate(false, new FactoryFlag());

        $this->assertNoViolation();
    }

    protected function createValidator(): FactoryFlagValidator
    {
        $this->entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $this->unitOfWorkProphecy = $this->prophesize(UnitOfWork::class);
        $this->securityProphecy = $this->prophesize(Security::class);

        $this->entityManagerProphecy->getUnitOfWork()->willReturn($this->unitOfWorkProphecy->reveal());

        return new FactoryFlagValidator(
            $this->entityManagerProphecy->reveal(),
            $this->securityProphecy->reveal()
        );
    }
}

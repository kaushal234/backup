<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallType;
use App\Validator\Constraints\Service\TechnicianOnCall\HasWarranty;
use App\Validator\Constraints\Service\TechnicianOnCall\HasWarrantyValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use LegacyBundle\Entity\WarrantyClaim;
use LegacyBundle\Manager\WarrantyClaimManager;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class HasWarrantyValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    private ObjectProphecy $entityManagerProphecy;
    private ObjectProphecy $warrantyClaimManager;
    private ObjectProphecy $unitOfWorkProphecy;

    public function testNoOriginalWarranty()
    {
        $this->setObject(new TechnicianOnCall());

        $this->validator->validate(true, new HasWarranty());
        $this->assertNoViolation();
    }

    public function testNoOriginalData()
    {
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->warrantyLegacyId = 1;

        $this->setObject($technicianOnCall);

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn([]);

        $this->validator->validate(true, new HasWarranty());
        $this->assertNoViolation();
    }

    public function testNoTypeChange()
    {
        $type = new TechnicianOnCallType();
        $reflection = new \ReflectionProperty($type::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($type, 1);

        $newType = new TechnicianOnCallType();
        $reflection = new \ReflectionProperty($newType::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($newType, 1);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->warrantyLegacyId = 1;
        $technicianOnCall->technicianOnCallType = $type;

        $this->setObject($technicianOnCall);

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn([
            'technicianOnCallType' => $type,
        ]);

        $this->validator->validate($newType, new HasWarranty());
        $this->assertNoViolation();
    }

    public function testIsFactory()
    {
        $type = new TechnicianOnCallType();
        $type->name = TechnicianOnCallType::FACTORY;
        $reflection = new \ReflectionProperty($type::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($type, 2);

        $newType = new TechnicianOnCallType();
        $reflection = new \ReflectionProperty($newType::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($newType, 1);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->warrantyLegacyId = 1;
        $technicianOnCall->technicianOnCallType = $type;

        $this->setObject($technicianOnCall);

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn([
            'technicianOnCallType' => $type,
        ]);

        $this->validator->validate($newType, new HasWarranty());
        $this->assertNoViolation();
    }

    public function testNoWarrantyFound()
    {
        $type = new TechnicianOnCallType();
        $type->name = TechnicianOnCallType::SSO;
        $reflection = new \ReflectionProperty($type::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($type, 2);

        $newType = new TechnicianOnCallType();
        $reflection = new \ReflectionProperty($newType::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($newType, 1);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->warrantyLegacyId = 1;
        $technicianOnCall->technicianOnCallType = $type;

        $this->setObject($technicianOnCall);

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn([
            'technicianOnCallType' => $type,
        ]);

        $this->warrantyClaimManager->findById($technicianOnCall->warrantyLegacyId)->willReturn(null);

        $this->validator->validate($newType, new HasWarranty());
        $this->assertNoViolation();
    }

    public function testWarrantyIsNotPending()
    {
        $type = new TechnicianOnCallType();
        $type->name = TechnicianOnCallType::SSO;
        $reflection = new \ReflectionProperty($type::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($type, 2);

        $newType = new TechnicianOnCallType();
        $reflection = new \ReflectionProperty($newType::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($newType, 1);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->warrantyLegacyId = 1;
        $technicianOnCall->technicianOnCallType = $type;

        $this->setObject($technicianOnCall);

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn([
            'technicianOnCallType' => $type,
        ]);

        $warrantyClaim = new WarrantyClaim();
        $warrantyClaim->status = 'REJECTED';

        $this->warrantyClaimManager->findById($technicianOnCall->warrantyLegacyId)->willReturn($warrantyClaim);

        $this->validator->validate($newType, new HasWarranty());
        $this->assertNoViolation();
    }

    public function testWarrantyIsPending()
    {
        $type = new TechnicianOnCallType();
        $type->name = TechnicianOnCallType::SSO;
        $reflection = new \ReflectionProperty($type::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($type, 2);

        $newType = new TechnicianOnCallType();
        $reflection = new \ReflectionProperty($newType::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($newType, 1);

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->warrantyLegacyId = 1;
        $technicianOnCall->technicianOnCallType = $type;

        $this->setObject($technicianOnCall);

        $this->unitOfWorkProphecy->getOriginalEntityData($technicianOnCall)->willReturn([
            'technicianOnCallType' => $type,
        ]);

        $warrantyClaim = new WarrantyClaim();
        $warrantyClaim->status = WarrantyClaim::PENDING;

        $this->warrantyClaimManager->findById($technicianOnCall->warrantyLegacyId)->willReturn($warrantyClaim);

        $this->validator->validate($newType, new HasWarranty());
        $this
            ->buildViolation('toc.messages.errors.warranty_open')
            ->assertRaised()
        ;
    }

    protected function createValidator(): HasWarrantyValidator
    {
        $this->entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $this->warrantyClaimManager = $this->prophesize(WarrantyClaimManager::class);

        $this->unitOfWorkProphecy = $this->prophesize(UnitOfWork::class);
        $this->entityManagerProphecy->getUnitOfWork()->willReturn($this->unitOfWorkProphecy->reveal());

        return new HasWarrantyValidator(
            $this->entityManagerProphecy->reveal(),
            $this->warrantyClaimManager->reveal()
        );
    }
}

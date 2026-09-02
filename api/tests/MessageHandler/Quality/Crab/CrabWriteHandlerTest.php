<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler\Quality\Crab;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Quality\Crab;
use App\Message\Quality\Crab\CrabWrite;
use App\MessageHandler\Quality\Crab\CrabWriteHandler;
use App\Notifier\Quality\Crab\CrabNotifier;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\EquipmentRecordManager;
use LegacyBundle\Manager\ModLogManager;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class CrabWriteHandlerTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testClosedCrabDoesNotYellowTagGreenTaggedEquipmentRecord(): void
    {
        $equipmentRecord = (new EquipmentRecord())->setLegacyId(1);
        $equipmentRecord->setGreenTagDate(new \DateTime('2026-05-28 22:17:08'));

        $crab = new Crab();
        $crab->equipmentRecord = $equipmentRecord;
        $crab->status = Crab::CLOSED;

        $user = new People();

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $equipmentRecordManagerProphecy = $this->prophesize(EquipmentRecordManager::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $notifierProphecy = $this->prophesize(CrabNotifier::class);
        $modLogManagerProphecy = $this->prophesize(ModLogManager::class);
        $messageProphecy = $this->prophesize(CrabWrite::class);

        $messageProphecy->getResourceIri()->willReturn('/crab/1');
        $messageProphecy->getUserIri()->willReturn('/user/1');
        $messageProphecy->getMethod()->willReturn(Request::METHOD_POST);

        $iriConverterProphecy->getResourceFromIri('/crab/1')->willReturn($crab);
        $iriConverterProphecy->getResourceFromIri('/user/1')->willReturn($user);

        $equipmentRecordManagerProphecy->findByLegacyId(1)->willReturn(['dgt_act' => '2026-05-28 22:17:08']);

        // A closed CRAB must not revert the unit to yellow tag.
        $entityManagerProphecy->persist(Argument::any())->shouldNotBeCalled();
        $entityManagerProphecy->flush()->shouldNotBeCalled();
        $modLogManagerProphecy->insertLog(Argument::cetera())->shouldNotBeCalled();
        $notifierProphecy->sendWrite($crab, $user, false)->shouldBeCalledOnce();

        $handler = new CrabWriteHandler(
            $iriConverterProphecy->reveal(),
            $equipmentRecordManagerProphecy->reveal(),
            $entityManagerProphecy->reveal(),
            $notifierProphecy->reveal(),
            $modLogManagerProphecy->reveal(),
        );
        $handler($messageProphecy->reveal());

        $this->assertNotNull($equipmentRecord->getGreenTagDate());
        $this->assertNull($equipmentRecord->getYellowTagDate());
    }

    public function testOpenCrabYellowTagsGreenTaggedEquipmentRecord(): void
    {
        $equipmentRecord = (new EquipmentRecord())->setLegacyId(1);
        $equipmentRecord->setGreenTagDate(new \DateTime('2026-05-28 22:17:08'));

        $crab = new Crab();
        $crab->equipmentRecord = $equipmentRecord;
        $crab->status = Crab::TO_FIX;
        $idProperty = new \ReflectionProperty(Crab::class, 'id');
        $idProperty->setValue($crab, 849702);

        $user = new People();

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $equipmentRecordManagerProphecy = $this->prophesize(EquipmentRecordManager::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $notifierProphecy = $this->prophesize(CrabNotifier::class);
        $modLogManagerProphecy = $this->prophesize(ModLogManager::class);
        $messageProphecy = $this->prophesize(CrabWrite::class);

        $messageProphecy->getResourceIri()->willReturn('/crab/1');
        $messageProphecy->getUserIri()->willReturn('/user/1');
        $messageProphecy->getMethod()->willReturn(Request::METHOD_POST);

        $iriConverterProphecy->getResourceFromIri('/crab/1')->willReturn($crab);
        $iriConverterProphecy->getResourceFromIri('/user/1')->willReturn($user);

        $equipmentRecordManagerProphecy->findByLegacyId(1)->willReturn(['dgt_act' => '2026-05-28 22:17:08']);

        // An open CRAB on a green-tagged unit reverts it to yellow tag.
        $entityManagerProphecy->persist($equipmentRecord)->shouldBeCalledOnce();
        $entityManagerProphecy->flush()->shouldBeCalledOnce();
        $modLogManagerProphecy->insertLog(1, 'ER', Argument::containingString('update from new CRAB'), $user)->shouldBeCalledOnce();
        $notifierProphecy->sendWrite($crab, $user, true)->shouldBeCalledOnce();

        $handler = new CrabWriteHandler(
            $iriConverterProphecy->reveal(),
            $equipmentRecordManagerProphecy->reveal(),
            $entityManagerProphecy->reveal(),
            $notifierProphecy->reveal(),
            $modLogManagerProphecy->reveal(),
        );
        $handler($messageProphecy->reveal());

        $this->assertNull($equipmentRecord->getGreenTagDate());
        $this->assertNotNull($equipmentRecord->getYellowTagDate());
    }
}

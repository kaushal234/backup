<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Purchasing\SupplierRanking;

use App\Entity\Directory\People;
use App\Entity\Purchasing\SupplierRanking\Classification;
use App\Entity\Purchasing\SupplierRanking\ExpertiseLevel;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\EventListener\Purchasing\SupplierRanking\SupplierRankingListener;
use App\Manager\Purchasing\SupplierRanking\ThresholdsManager;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SupplierRankingListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testUpdateSupplierRanking()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $thresholdsManagerProphecy = $this->prophesize(ThresholdsManager::class);
        $requestProphecy = $this->prophesize(Request::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $supplierRanking = new SupplierRanking();
        $supplierRanking->classification = new Classification();
        $supplierRanking->classification->name = 'Current classification';
        $supplierRanking->expertiseLevel = new ExpertiseLevel();
        $supplierRanking->lastScreeningAt = new \DateTime();
        $previousSupplierRanking = new SupplierRanking();
        $previousSupplierRanking->lastScreeningAt = new \DateTime('2020-01-01');
        $user = new People();

        $bagProphecy = $this->prophesize(ParameterBag::class);
        $bagProphecy->get('previous_data')->willReturn($previousSupplierRanking)->shouldBeCalledOnce();
        $bagProphecy->get('data')->willReturn($supplierRanking)->shouldBeCalledOnce();
        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->attributes = $bagProphecy->reveal();

        $newClassification = new Classification();
        $newClassification->name = 'New classification';
        $serviceLocatorProphecy->get(ThresholdsManager::class)->shouldBeCalledOnce()->willReturn($thresholdsManagerProphecy->reveal());
        $thresholdsManagerProphecy->findNewClassification(Argument::cetera())->shouldBeCalledOnce()->willReturn($newClassification);
        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldBeCalledOnce()->willReturn($entityManagerProphecy->reveal());
        $entityManagerProphecy->flush()->shouldBeCalledOnce();
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledOnce()->willReturn($securityProphecy->reveal());
        $securityProphecy->getUser()->shouldBeCalledOnce()->willReturn($user);

        $listener = new SupplierRankingListener($serviceLocatorProphecy->reveal());
        $listener->updateClassification(new RequestEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST));

        $this->assertSame($supplierRanking->classification, $newClassification);
        $this->assertSame($supplierRanking->lastScreeningBy, $user);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Manager\Transfer;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Entity\Sales\SalesForecast;
use App\Manager\Transfer\Handler\SalesForecastAdvancedTransferHandler;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class SalesForecastAdvancedTransferManagerTest extends TestCase
{
    use ProphecyTrait;

    public function testNothingHappensWithoutASSO()
    {
        $repositoryMock = $this->getMockBuilder(SalesForecastRepository::class)->disableOriginalConstructor()->onlyMethods(['createQueryBuilder'])->getMock();

        $entityManagerProphecy = $this->prophesize(EntityManager::class);
        $entityManagerProphecy->getRepository(SalesForecast::class)->shouldNotBeCalled();

        $entityManager = $entityManagerProphecy->reveal();

        $repositoryMock->expects($this->never())->method('createQueryBuilder');

        $accessor = $this->prophesize(PropertyAccessor::class);

        $handler = new SalesForecastAdvancedTransferHandler();
        $handler->setEntityManager($entityManager)->setPropertyAccessor($accessor->reveal());

        $target = new People();

        $handler->handle(null, $target, new OwnerReflectionBag(new \ReflectionClass(SalesForecast::class), new \ReflectionProperty(SalesForecast::class, 'asm')));
    }

    public function testAnUpdateQueryIsGeneratedWithSSO()
    {
        $repositoryMock = $this->getMockBuilder(SalesForecastRepository::class)->disableOriginalConstructor()->onlyMethods(['createQueryBuilder'])->getMock();

        $entityManagerProphecy = $this->prophesize(EntityManager::class);
        $entityManagerProphecy->getRepository(SalesForecast::class)->shouldBeCalledTimes(1)->willReturn($repositoryMock);

        $mockedException = new \Exception('Tricky patch to avoid mocking too deep');
        $entityManagerProphecy->createQuery('SELECT WHERE o.status IN (:allowedStatuses) AND o.sso = :sso')->shouldBeCalledTimes(1)->willThrow($mockedException);

        $entityManager = $entityManagerProphecy->reveal();

        $qb = new QueryBuilder($entityManager);

        $repositoryMock->expects($this->once())->method('createQueryBuilder')->with('o')->willReturn($qb);

        $accessor = $this->prophesize(PropertyAccessor::class);

        $handler = new SalesForecastAdvancedTransferHandler();
        $handler->setEntityManager($entityManager)->setPropertyAccessor($accessor->reveal());

        $target = new People();
        $sso = new Location();

        try {
            $handler->handle(
                null,
                $target,
                new OwnerReflectionBag(new \ReflectionClass(SalesForecast::class), new \ReflectionProperty(SalesForecast::class, 'asm')),
                [
                    'sso' => $sso,
                ]
            );
        } catch (\Exception $exception) {
            if ($mockedException !== $exception) {
                throw $exception;
            }
        }

        self::assertCount(2, $qb->getParameters());

        self::assertNotNull($qb->getParameter('allowedStatuses'));
        self::assertSame($qb->getParameter('allowedStatuses')->getValue(), SalesForecast::OPEN_STATUSES);

        self::assertNotNull($qb->getParameter('sso'));
        self::assertSame($qb->getParameter('sso')->getValue(), $sso);
    }

    public function testAnUpdateQueryIsGeneratedWithFullParameters()
    {
        $repositoryMock = $this->getMockBuilder(SalesForecastRepository::class)->disableOriginalConstructor()->onlyMethods(['createQueryBuilder'])->getMock();

        $entityManagerProphecy = $this->prophesize(EntityManager::class);
        $entityManagerProphecy->getRepository(SalesForecast::class)->shouldBeCalledTimes(1)->willReturn($repositoryMock);

        $mockedException = new \Exception('Tricky patch to avoid mocking too deep');
        $entityManagerProphecy->createQuery('SELECT WHERE o.status IN (:allowedStatuses) AND o.sso = :sso AND o.asm = :asmSource AND o.buyer = :buyer AND o.endUser = :endUser')->shouldBeCalledTimes(1)->willThrow($mockedException);

        $entityManager = $entityManagerProphecy->reveal();

        $qb = new QueryBuilder($entityManager);

        $repositoryMock->expects($this->once())->method('createQueryBuilder')->with('o')->willReturn($qb);

        $accessor = $this->prophesize(PropertyAccessor::class);

        $handler = new SalesForecastAdvancedTransferHandler();
        $handler->setEntityManager($entityManager)->setPropertyAccessor($accessor->reveal());

        $target = new People();
        $sso = new Location();
        $asmSource = new People();
        $buyer = new Customer();
        $endUser = new Customer();

        try {
            $handler->handle(
                null,
                $target,
                new OwnerReflectionBag(new \ReflectionClass(SalesForecast::class), new \ReflectionProperty(SalesForecast::class, 'asm')),
                [
                    'sso' => $sso,
                    'asmSource' => $asmSource,
                    'buyer' => $buyer,
                    'endUser' => $endUser,
                ]
            );
        } catch (\Exception $exception) {
            if ($mockedException !== $exception) {
                throw $exception;
            }
        }

        self::assertCount(5, $qb->getParameters());

        self::assertNotNull($qb->getParameter('allowedStatuses'));
        self::assertSame($qb->getParameter('allowedStatuses')->getValue(), SalesForecast::OPEN_STATUSES);

        self::assertNotNull($qb->getParameter('sso'));
        self::assertSame($qb->getParameter('sso')->getValue(), $sso);

        self::assertNotNull($qb->getParameter('asmSource'));
        self::assertSame($qb->getParameter('asmSource')->getValue(), $asmSource);

        self::assertNotNull($qb->getParameter('buyer'));
        self::assertSame($qb->getParameter('buyer')->getValue(), $buyer);

        self::assertNotNull($qb->getParameter('endUser'));
        self::assertSame($qb->getParameter('endUser')->getValue(), $endUser);
    }
}

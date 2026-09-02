<?php

declare(strict_types=1);

namespace App\Tests\Manager\Transfer;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Manager\Transfer\Handler\MeetingTransferHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class MeetingTransferHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testHandle()
    {
        $repositoryMock = $this->createMock(EntityRepository::class);

        $entityManagerProphecy = $this->prophesize(EntityManager::class);
        $entityManagerProphecy->getRepository(Meeting::class)->shouldBeCalledTimes(1)->willReturn($repositoryMock);

        $mockedException = new \Exception('Tricky patch to avoid mocking too deep');
        $entityManagerProphecy->createQuery('SELECT WHERE o.createdBy = :source AND o.status != :closed')->shouldBeCalledTimes(1)->willThrow($mockedException);

        $entityManager = $entityManagerProphecy->reveal();

        $qb = new QueryBuilder($entityManager);

        $repositoryMock->expects($this->once())->method('createQueryBuilder')->with('o')->willReturn($qb);

        $accessor = $this->prophesize(PropertyAccessor::class);

        $handler = new MeetingTransferHandler();
        $handler->setEntityManager($entityManager)->setPropertyAccessor($accessor->reveal());

        $target = new People();
        $source = new People();

        try {
            $handler->handle(
                $source,
                $target,
                new OwnerReflectionBag(new \ReflectionClass(Meeting::class), new \ReflectionProperty(Meeting::class, 'createdBy')));
        } catch (\Exception $exception) {
            if ($mockedException !== $exception) {
                throw $exception;
            }
        }

        self::assertCount(2, $qb->getParameters());
        self::assertNotNull($qb->getParameter('closed'));
        self::assertNotNull($qb->getParameter('source'));
        self::assertSame($qb->getParameter('closed')->getValue(), Meeting::CLOSED);
        self::assertSame($qb->getParameter('source')->getValue(), $source);
    }
}

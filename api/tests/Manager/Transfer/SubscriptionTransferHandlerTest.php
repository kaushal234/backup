<?php

declare(strict_types=1);

namespace App\Tests\Manager\Transfer;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Manager\Transfer\Handler\SubscriptionTransferHandler;
use App\Repository\Common\SubscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class SubscriptionTransferHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testASubscriptionIsDeletedWhenTheUserHasBeenTransferred()
    {
        $subscriptions = [new Subscription(), new Subscription()];
        $source = new People();
        $target = new People();

        $repositoryMock = $this->getMockBuilder(SubscriptionRepository::class)->disableOriginalConstructor()->onlyMethods(['createQueryBuilder'])->getMock();

        $entityManagerProphecy = $this->prophesize(EntityManager::class);
        $entityManagerProphecy->getRepository(Subscription::class)->shouldBeCalledTimes(1)->willReturn($repositoryMock);

        $argumentAssertion = Argument::that(static fn (Subscription $subscription) => \in_array($subscription, $subscriptions, true));

        $entityManagerProphecy->refresh($argumentAssertion)->shouldBeCalledTimes(2);
        $entityManagerProphecy->remove($argumentAssertion)->shouldBeCalledTimes(2);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $entityManager = $entityManagerProphecy->reveal();

        $queryProphecy = $this->prophesize(Query::class);
        $queryProphecy->getResult()->shouldBeCalledTimes(1)->willReturn($subscriptions);
        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryBuilderProphecy->where('o.user = :source')->shouldBeCalledTimes(1)->willReturn($queryBuilderProphecy->reveal());
        $queryBuilderProphecy->setParameters(new ArrayCollection([new Parameter('source', $source)]))->shouldBeCalledTimes(1)->willReturn($queryBuilderProphecy->reveal());
        $queryBuilderProphecy->getQuery()->shouldBeCalledTimes(1)->willReturn($queryProphecy->reveal());

        $repositoryMock->expects($this->once())->method('createQueryBuilder')->with('o')->willReturn($queryBuilderProphecy->reveal());

        $handler = new SubscriptionTransferHandler();
        $handler->setEntityManager($entityManager);

        $handler->handle(
            $source,
            $target,
            new OwnerReflectionBag(new \ReflectionClass(Subscription::class), new \ReflectionProperty(Subscription::class, 'user'))
        );
    }
}

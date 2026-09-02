<?php

declare(strict_types=1);

namespace Agile\Repository;

use App\Agile\Event\LogUserUpdateOnClientEvent;
use App\Agile\Repository\UserRepository;
use App\Agile\Resources\User;
use App\Agile\UserEventResolver;
use App\Http\AgileGetClient;
use App\Http\AgilePostClient;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserRepositoryTest extends KernelTestCase
{
    use ProphecyTrait;

    private UserRepository $userRepository;
    private $agileGetClient;
    private $agilePostClient;
    private $serializer;
    private $eventDispatcher;

    protected function setUp(): void
    {
        $this->agileGetClient = $this->prophesize(AgileGetClient::class);
        $this->agilePostClient = $this->prophesize(AgilePostClient::class);
        $this->serializer = $this->getContainer()->get(SerializerInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->userRepository = new UserRepository(
            $this->agileGetClient->reveal(),
            $this->agilePostClient->reveal(),
            $this->serializer,
            $this->eventDispatcher->reveal()
        );
    }

    public function testGetAllUsers(): void
    {
        $agileUser1 = new User();
        $agileUser1->peopleId = '1234';
        $agileUser1->jobTitle = 'footballer';
        $agileUser1->setActive('new');
        $agileUser2 = new User();
        $agileUser2->peopleId = '12345';
        $agileUser2->jobTitle = 'president';
        $agileUser2->setActive('inactive');

        $data = [
            'results' => [
                [
                    'id' => 'idAgile1',
                    'ref' => '1234',
                    'jobTitle' => 'footballer',
                    'status' => 'new',
                ],
                [
                    'id' => 'idAgile2',
                    'ref' => '12345',
                    'jobTitle' => 'president',
                    'status' => 'inactive',
                ],
            ],
        ];

        $agileGetClient = $this->prophesize(AgileGetClient::class);
        $agileGetClient->doRequest(['page' => 1, 'perPage' => 1000])->shouldBeCalledTimes(1)->willReturn($data);
        $agileGetClient->doRequest(['page' => 2, 'perPage' => 1000])->shouldBeCalledTimes(1)->willReturn(['results' => []]);

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $repository = new UserRepository($agileGetClient->reveal(), $this->prophesize(AgilePostClient::class)->reveal(), $this->serializer, $eventDispatcher->reveal());

        $result = $repository->getAllUsers();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('idAgile1', $result);
        $this->assertArrayHasKey('idAgile2', $result);
        $this->assertSame($agileUser1->peopleId, $result['idAgile1']->peopleId);
        $this->assertSame($agileUser2->peopleId, $result['idAgile2']->peopleId);
        $this->assertSame($agileUser1->jobTitle, $result['idAgile1']->jobTitle);
        $this->assertSame($agileUser2->jobTitle, $result['idAgile2']->jobTitle);
        $this->assertSame($agileUser1->isActive(), $result['idAgile1']->isActive());
        $this->assertSame($agileUser2->isActive(), $result['idAgile2']->isActive());
    }

    /**
     * @dataProvider provideEventTypes
     */
    public function testUpdateAgileUserSuccess(string $event): void
    {
        $user = new User();
        $user->email = 'test@example.com';
        $changes = ['name' => 'New Name'];

        $agilePostClient = $this->prophesize(AgilePostClient::class);
        $agilePostClient->doRequest($user, $event)->shouldBeCalledOnce()->willReturn([]);

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new LogUserUpdateOnClientEvent($user, $changes))->shouldBeCalledOnce();

        $repository = new UserRepository($this->prophesize(AgileGetClient::class)->reveal(), $agilePostClient->reveal(), $this->prophesize(SerializerInterface::class)->reveal(), $eventDispatcher->reveal());

        $repository->updateAgileUser($user, $changes, $event);
    }

    /**
     * @dataProvider provideEventTypes
     */
    public function testUpdateAgileUserFailure(string $event): void
    {
        $user = new User();
        $user->email = 'test@example.com';
        $changes = ['name' => 'New Name'];
        $codeError = 500;
        $errorMessage = 'Internal Server Error';

        $exception = new \Exception('Invalid response on '.$event.' user: '.$user->email.' errorCode:'.$codeError.' errorMessage:'.$errorMessage);

        $agilePostClient = $this->prophesize(AgilePostClient::class);
        $agilePostClient->doRequest($user, $event)->shouldBeCalledOnce()->willThrow($exception);

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new LogUserUpdateOnClientEvent($user, $changes))->shouldNotBeCalled();

        $repository = new UserRepository($this->prophesize(AgileGetClient::class)->reveal(), $agilePostClient->reveal(), $this->prophesize(SerializerInterface::class)->reveal(), $eventDispatcher->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid response on '.$event.' user: test@example.com errorCode:500 errorMessage:Internal Server Error');

        $repository->updateAgileUser($user, $changes, $event);
    }

    public function provideEventTypes(): array
    {
        return [
            [UserEventResolver::USER_JOINED],
            [UserEventResolver::USER_SUSPENDED],
            [UserEventResolver::USER_UPDATED],
        ];
    }
}

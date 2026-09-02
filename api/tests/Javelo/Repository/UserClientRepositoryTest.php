<?php

declare(strict_types=1);

namespace App\Tests\Javelo\Repository;

use App\Entity\Directory\People;
use App\Http\JaveloClient;
use App\Javelo\Event\LogUserClientEvent;
use App\Javelo\Repository\UserClientRepository;
use App\Javelo\Resources\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class UserClientRepositoryTest extends TestCase
{
    use ProphecyTrait;

    public function testGetUserById(): void
    {
        $javeloUser = new User();
        $javeloUserJson = json_encode($javeloUser);
        $serializer = $this->prophesize(SerializerInterface::class);
        $serializer->deserialize($javeloUserJson, User::class, 'json')->willReturn($javeloUser);

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(Response::HTTP_OK);
        $response->getContent()->willReturn($javeloUserJson);

        $javeloClient = $this->prophesize(JaveloClient::class);
        $javeloClient->doUserRequest([], '1')->willReturn($response->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $repository = new UserClientRepository($javeloClient->reveal(), $serializer->reveal(), $eventDispatcher->reveal());
        $result = $repository->getUserById('1');

        $this->assertInstanceOf(User::class, $result);
    }

    public function testGetUserByIdThrowsExceptionError(): void
    {
        $serializer = $this->prophesize(SerializerInterface::class);
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(Response::HTTP_INTERNAL_SERVER_ERROR);

        $javeloClient = $this->prophesize(JaveloClient::class);
        $javeloClient->doUserRequest([], '1')->willReturn($response->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $repository = new UserClientRepository($javeloClient->reveal(), $serializer->reveal(), $eventDispatcher->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid response on getUserById id: 1');
        $repository->getUserById('1');
    }

    public function testFindUserByEmail(): void
    {
        $javeloUser = new User();
        $javeloUserDataJson = json_encode($javeloUser);

        $serializer = $this->prophesize(SerializerInterface::class);
        $serializer->deserialize($javeloUserDataJson, User::class, 'json')->willReturn($javeloUser);

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(Response::HTTP_OK);
        $response->getContent()->willReturn(json_encode(['Resources' => [json_decode($javeloUserDataJson, true)]]));

        $javeloClient = $this->prophesize(JaveloClient::class);
        $email = 'example@example.com';
        $javeloClient->doUserRequest(['filter' => 'userName eq '.$email])->willReturn($response->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $repository = new UserClientRepository($javeloClient->reveal(), $serializer->reveal(), $eventDispatcher->reveal());
        $result = $repository->findUserByEmail($email);

        $serializer->deserialize($javeloUserDataJson, User::class, 'json')->shouldHaveBeenCalled();

        $this->assertInstanceOf(User::class, $result);
    }

    public function testFindUserByEmailThrowsExceptionError(): void
    {
        $serializer = $this->prophesize(SerializerInterface::class);

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(Response::HTTP_INTERNAL_SERVER_ERROR);

        $javeloClient = $this->prophesize(JaveloClient::class);
        $email = 'example@example.com';
        $javeloClient->doUserRequest(['filter' => 'userName eq '.$email])->willReturn($response->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $repository = new UserClientRepository($javeloClient->reveal(), $serializer->reveal(), $eventDispatcher->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid response on findUserMethod email: example@example.com');

        $repository->findUserByEmail($email);
    }

    public function testUpdateUser(): void
    {
        $javeloUser = new User();
        $javeloUser->id = 'javeloId';
        $javeloUser->externalId = '15';

        $changes = ['lastname' => ['before', 'after']];

        $serializer = $this->prophesize(SerializerInterface::class);
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(Response::HTTP_OK);

        $javeloClient = $this->prophesize(JaveloClient::class);
        $javeloClient->doUserRequest(['body' => $javeloUser], $javeloUser->id, Request::METHOD_PATCH)->shouldBeCalled()->willReturn($response->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $poster = new People();

        $this->assertNotNull($javeloUser->id, 'Log Event, at update user, have a Javelo ID not Null.');
        $eventDispatcher->dispatch(new LogUserClientEvent($javeloUser, $changes, $poster))->shouldBeCalled();
        $repository = new UserClientRepository($javeloClient->reveal(), $serializer->reveal(), $eventDispatcher->reveal());

        $repository->updateUser($javeloUser, $changes, $poster);
    }

    public function testUpdateUserThrowsExceptionError(): void
    {
        $javeloUser = new User();
        $javeloUser->id = 'javeloId';
        $javeloUser->externalId = '15';
        $javeloUser->userName = 'javeloUser@Javelo.fr';

        $serializer = $this->prophesize(SerializerInterface::class);

        $response = $this->prophesize(ResponseInterface::class);
        $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $response->getStatusCode()->willReturn($errorCode);
        $errorResponse = 'response error update';
        $response->getContent()->willReturn($errorResponse);

        $javeloClient = $this->prophesize(JaveloClient::class);
        $javeloClient->doUserRequest(['body' => $javeloUser], $javeloUser->id, Request::METHOD_PATCH)->willReturn($response->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $repository = new UserClientRepository($javeloClient->reveal(), $serializer->reveal(), $eventDispatcher->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid response on update user: javeloUser@Javelo.fr errorCode:'.$errorCode.' errorMessage:'.$errorResponse);

        $repository->updateUser($javeloUser, []);
    }

    public function testCreateUser(): void
    {
        $javeloUser = new User();
        $javeloUserDataJson = json_encode($javeloUser);
        $changes = ['lastname' => [null, 'after']];
        $serializer = $this->prophesize(SerializerInterface::class);
        $serializer->deserialize($javeloUserDataJson, User::class, 'json')->willReturn(new User());

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(Response::HTTP_CREATED);
        $response->toArray()->shouldBeCalled()->willReturn(['id' => 'newIdUser']);

        $javeloClient = $this->prophesize(JaveloClient::class);
        $javeloClient->doUserRequest(['body' => $javeloUser], null, method: Request::METHOD_POST)->willReturn($response->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $poster = new People();
        $this->assertNull($javeloUser->id, 'Log Event, at create user, have a Javelo ID null.');
        $eventDispatcher->dispatch(new LogUserClientEvent($javeloUser, $changes, $poster))->shouldBeCalled();

        $repository = new UserClientRepository($javeloClient->reveal(), $serializer->reveal(), $eventDispatcher->reveal());

        $this->assertNull($javeloUser->id);
        $repository->createUser($javeloUser, $changes, $poster);
        $this->assertSame($javeloUser->id, 'newIdUser', 'Javelo ID have to be set after log to control user groups.');

        $this->assertSame('newIdUser', $javeloUser->id, 'Need set id on JaveloUser after creation to update group later');

        $serializer->deserialize($javeloUserDataJson, User::class, 'json')->shouldNotHaveBeenCalled();
    }

    public function testCreateUserThrowsExceptionError(): void
    {
        $javeloUser = new User();
        $javeloUser->externalId = '12';
        $javeloUser->userName = 'javeloUser@Javelo.fr';

        $serializer = $this->prophesize(SerializerInterface::class);

        $response = $this->prophesize(ResponseInterface::class);
        $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $response->getStatusCode()->willReturn($errorCode);
        $errorResponse = 'response error create';
        $response->getContent()->willReturn($errorResponse);

        $javeloClient = $this->prophesize(JaveloClient::class);
        $javeloClient->doUserRequest(['body' => $javeloUser], null, method: Request::METHOD_POST)->willReturn($response->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $repository = new UserClientRepository($javeloClient->reveal(), $serializer->reveal(), $eventDispatcher->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid response on create user: javeloUser@Javelo.fr errorCode:'.$errorCode.' errorMessage:'.$errorResponse);

        $repository->createUser($javeloUser, []);
    }

    public function testGetAllUsers(): void
    {
        $javeloUser1 = new User();
        $javeloUser1->id = 'JaveloId1';
        $javeloUser2 = new User();
        $javeloUser2->id = 'JaveloId2';

        $dataNotEmpty = [$javeloUser1, $javeloUser2];
        $javeloUserArrayJsonNotEmpty = json_encode(['Resources' => $dataNotEmpty]);

        $dataEmpty = [];
        $javeloUserArrayJsonEmpty = json_encode(['Resources' => $dataEmpty]);

        $serializer = $this->prophesize(SerializerInterface::class);
        $serializer->deserialize(json_encode($javeloUser1), User::class, 'json')->willReturn($javeloUser1);
        $serializer->deserialize(json_encode($javeloUser2), User::class, 'json')->willReturn($javeloUser2);

        $responseNotEmpty = $this->prophesize(ResponseInterface::class);
        $responseNotEmpty->getContent()->shouldBeCalledTimes(2)->willReturn($javeloUserArrayJsonNotEmpty, $javeloUserArrayJsonEmpty);
        $responseNotEmpty->getStatusCode()->shouldBeCalledTimes(2)->willReturn(Response::HTTP_OK);

        $javeloClient = $this->prophesize(JaveloClient::class);
        $javeloClient->doUserRequest(['startIndex' => 1])->shouldBeCalledTimes(1)->willReturn($responseNotEmpty->reveal());
        $javeloClient->doUserRequest(['startIndex' => 3])->shouldBeCalledTimes(1)->willReturn($responseNotEmpty->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $repository = new UserClientRepository($javeloClient->reveal(), $serializer->reveal(), $eventDispatcher->reveal());

        $result = $repository->getAllUsers();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(User::class, $result[0]);
        $this->assertInstanceOf(User::class, $result[1]);
    }

    public function testGetAllUsersThrowsExceptionErrorDuringPagination(): void
    {
        $serializer = $this->prophesize(SerializerInterface::class);

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->getContent()->willReturn('une belle error');

        $javeloClient = $this->prophesize(JaveloClient::class);
        $javeloClient->doUserRequest(['startIndex' => 1])->willReturn($response->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $repository = new UserClientRepository($javeloClient->reveal(), $serializer->reveal(), $eventDispatcher->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('une belle error');

        $repository->getAllUsers();
    }

    public function testGetAllUsersThrowsExceptionErrorFirstCall(): void
    {
        $serializer = $this->prophesize(SerializerInterface::class);

        $dataEmpty = [];
        $javeloUserArrayJsonEmpty = json_encode(['Resources' => $dataEmpty]);

        $firstResponseEmpty = $this->prophesize(ResponseInterface::class);
        $firstResponseEmpty->getContent()->shouldBeCalledTimes(1)->willReturn($javeloUserArrayJsonEmpty);
        $firstResponseEmpty->getStatusCode()->shouldBeCalledTimes(1)->willReturn(Response::HTTP_OK);

        $javeloClient = $this->prophesize(JaveloClient::class);
        $javeloClient->doUserRequest(['startIndex' => 1])->willReturn($firstResponseEmpty->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $repository = new UserClientRepository($javeloClient->reveal(), $serializer->reveal(), $eventDispatcher->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('no users on Javelo');

        $repository->getAllUsers();
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Javelo\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Javelo\Event\UpdateJaveloUserEvent;
use App\Javelo\EventListener\UpdateJaveloUserListener;
use App\Javelo\Factory\UserFactory;
use App\Javelo\Message\CreateUserMessage;
use App\Javelo\Message\UpdateUserMessage;
use App\Javelo\Repository\UserClientRepository;
use App\Javelo\Repository\UserRepository;
use App\Javelo\Resources\User;
use App\Javelo\UserComparator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateJaveloUserListenerTest extends TestCase
{
    use ProphecyTrait;

    private UpdateJaveloUserListener $listener;
    private $userRepository;

    private $userFactory;
    private $userComparator;
    private $messageBus;
    private $userClientRepository;
    private $logger;
    private $iriConverter;
    private $security;

    protected function setUp(): void
    {
        $this->userRepository = $this->prophesize(UserRepository::class);
        $this->userFactory = $this->prophesize(UserFactory::class);
        $this->userComparator = $this->prophesize(UserComparator::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->userClientRepository = $this->prophesize(UserClientRepository::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->iriConverter = $this->prophesize(IriConverterInterface::class);
        $this->security = $this->prophesize(Security::class);

        $this->listener = new UpdateJaveloUserListener(
            $this->userRepository->reveal(),
            $this->userFactory->reveal(),
            $this->userComparator->reveal(),
            $this->messageBus->reveal(),
            $this->userClientRepository->reveal(),
            $this->logger->reveal(),
            $this->iriConverter->reveal(),
            $this->security->reveal()
        );
    }

    /**
     * @dataProvider provideTestData
     */
    public function testInvoke(array $peopleData, array $changes, ?User $javeloUser, string $expectedMessageClass, string $emailSearchInJavelo, ?People $poster = new People()): void
    {
        $updatedPeople = $this->prophesize(People::class);
        $updatedPeople->getId()->willReturn($peopleData['id']);
        $updatedPeople->getUsername()->willReturn($peopleData['username']);

        $javeloUserToUpdate = new User();
        $javeloUserToUpdate->active = true;

        $this->userRepository
            ->searchPeopleWithAclAuthJavelo($peopleData['id'])->shouldBeCalledTimes(1)
            ->willReturn([['id' => $peopleData['id']]]);

        if (!\array_key_exists('supervisor', $peopleData)) {
            $supervisor = $this->prophesize(People::class);
            $supervisor->getId()->willReturn(12);
            $updatedPeople->getSupervisor()->shouldBeCalledTimes(1)->willReturn($supervisor->reveal());
            $updatedPeople->reveal();

            $this->userRepository
                ->searchPeopleWithAclAuthJavelo(12)->shouldBeCalledTimes(1)
                ->willReturn([['id' => 12]]);
            $this->userFactory->createFromPeople($updatedPeople, $javeloUser?->id, [['id' => $peopleData['id']], ['id' => 12]])->willReturn($javeloUserToUpdate);
        }

        if (\array_key_exists('supervisor', $peopleData) && null === $peopleData['supervisor']) {
            $updatedPeople->getSupervisor()->shouldBeCalledTimes(1)->willReturn($peopleData['supervisor']);
            $this->userFactory->createFromPeople($updatedPeople, $javeloUser?->id, [['id' => $peopleData['id']]])->willReturn($javeloUserToUpdate);
        }

        if (null !== $javeloUser) {
            $this->userClientRepository
                ->findUserByEmail($emailSearchInJavelo)
                ->willReturn($javeloUser);
        } else {
            $this->userClientRepository
                ->findUserByEmail($emailSearchInJavelo)
                ->willReturn(null);
        }

        $this->userComparator->getChanges($javeloUserToUpdate, $javeloUser)->willReturn($changes);

        if (null !== $poster) {
            $posterIri = '/people/12';
            $this->security->getUser()->willReturn($poster);
            $this->iriConverter
                ->getIriFromResource($poster)
                ->willReturn($posterIri);
        } else {
            $posterIri = null;
            $this->security->getUser()->willReturn(null);
            $this->iriConverter->getIriFromResource(Argument::any())->shouldNotHaveBeenCalled();
        }

        if (UpdateUserMessage::class === $expectedMessageClass) {
            $message = new UpdateUserMessage($javeloUserToUpdate, $changes, $posterIri);
        } else {
            $message = new CreateUserMessage($javeloUserToUpdate, $changes, $posterIri);
        }

        $this->messageBus
            ->dispatch($message)
            ->shouldBeCalled()
            ->willReturn(new Envelope(new \stdClass()));

        $event = new UpdateJaveloUserEvent($updatedPeople->reveal(), $changes);
        ($this->listener)($event);
    }

    public function provideTestData(): array
    {
        $javeloUserTest = new User();
        $javeloUserTest->id = 'javeloId';

        return [
            'User Update on username' => [
                'peopleData' => ['id' => 1, 'username' => 'updateUsername.com'],
                'changes' => ['username' => ['previousUsername.com', 'updateUsername.com']],
                'javeloUser' => $javeloUserTest,
                'expectedMessageClass' => UpdateUserMessage::class,
                'emailSearchInJavelo' => 'previousUsername.com',
            ],
            'User Update' => [
                'peopleData' => ['id' => 1, 'username' => 'myEmail.com'],
                'changes' => ['someProperty' => ['blah', 'blahblah']],
                'javeloUser' => $javeloUserTest,
                'expectedMessageClass' => UpdateUserMessage::class,
                'emailSearchInJavelo' => 'myEmail.com',
            ],
            'User Create' => [
                'peopleData' => ['id' => 2, 'username' => 'newUser@example.com'],
                'changes' => ['someProperty' => [null, 'blahblah']],
                'javeloUser' => null,
                'expectedMessageClass' => CreateUserMessage::class,
                'emailSearchInJavelo' => 'newUser@example.com',
            ],
            'User Update with null poster' => [
                'peopleData' => ['id' => 2, 'username' => 'newUser@example.com'],
                'changes' => ['someProperty' => [null, 'blahblah']],
                'javeloUser' => null,
                'expectedMessageClass' => CreateUserMessage::class,
                'emailSearchInJavelo' => 'newUser@example.com',
                'poster' => null,
            ],
            'User Create with null poster' => [
                'peopleData' => ['id' => 2, 'username' => 'newUser@example.com'],
                'changes' => ['someProperty' => [null, 'blahblah']],
                'javeloUser' => null,
                'expectedMessageClass' => CreateUserMessage::class,
                'emailSearchInJavelo' => 'newUser@example.com',
                'poster' => null,
            ],
            'User with no supervisor Update on username' => [
                'peopleData' => ['id' => 1, 'username' => 'updateUsername.com', 'supervisor' => null],
                'changes' => ['username' => ['previousUsername.com', 'updateUsername.com']],
                'javeloUser' => $javeloUserTest,
                'expectedMessageClass' => UpdateUserMessage::class,
                'emailSearchInJavelo' => 'previousUsername.com',
            ],
        ];
    }

    public function testInvokeWithExceptionDuringUserSearch(): void
    {
        $people = $this->prophesize(People::class);
        $people->getId()->willReturn(1);
        $people->getUsername()->willReturn('jdoe@example.com');

        $event = $this->prophesize(UpdateJaveloUserEvent::class);
        $event->getPeople()->willReturn($people->reveal());
        $event->getChanges()->willReturn([]);

        $this->userRepository->searchPeopleWithAclAuthJavelo(1)->willReturn(['jdoe@example.com']);
        $this->userClientRepository->findUserByEmail('jdoe@example.com')->willThrow(new \Exception('Database error'));

        $this->logger->error('Something went wrong when search {email} on Javelo: {error}', [
            'email' => 'jdoe@example.com',
            'error' => 'Database error',
        ])->shouldBeCalled();

        $this->listener->__invoke($event->reveal());

        $this->messageBus->dispatch()->shouldNotHaveBeenCalled();
    }
}

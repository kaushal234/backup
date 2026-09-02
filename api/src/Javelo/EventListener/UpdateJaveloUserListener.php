<?php

declare(strict_types=1);

namespace App\Javelo\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Javelo\Event\UpdateJaveloUserEvent;
use App\Javelo\Factory\UserFactory;
use App\Javelo\Message\CreateUserMessage;
use App\Javelo\Message\UpdateUserMessage;
use App\Javelo\Repository\UserClientRepository;
use App\Javelo\Repository\UserRepository;
use App\Javelo\Resources\User;
use App\Javelo\UserComparator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener]
class UpdateJaveloUserListener
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserFactory $userFactory,
        private readonly UserComparator $userComparator,
        private readonly MessageBusInterface $messageBus,
        private readonly UserClientRepository $javeloUserRepository,
        private readonly LoggerInterface $javeloRequestLogger,
        private readonly IriConverterInterface $iriConverter,
        private readonly Security $security
    ) {
    }

    public function __invoke(UpdateJaveloUserEvent $event): void
    {
        $updatedPeople = $event->getPeople();

        if (empty($peopleIsSynchronized = $this->userRepository->searchPeopleWithAclAuthJavelo($updatedPeople->getId()))) {
            return;
        }

        $email = $updatedPeople->getUsername();
        if (\array_key_exists('username', $event->getChanges())) {
            $email = $event->getChanges()['username'][0];
        }

        try {
            $previousJaveloUser = $this->javeloUserRepository->findUserByEmail($email);
        } catch (\Exception $exception) {
            $this->javeloRequestLogger->error('Something went wrong when search {email} on Javelo: {error}', [
                'email' => $email,
                'error' => $exception->getMessage(),
            ]);

            return;
        }
        $supervisorId = $updatedPeople->getSupervisor()?->getId();
        $supervisorIsSynchronized = null !== $supervisorId
            ? $this->userRepository->searchPeopleWithAclAuthJavelo($supervisorId)
            : [];

        $javeloUserToUpdate = $this->userFactory->createFromPeople($updatedPeople, $previousJaveloUser?->id, array_merge($peopleIsSynchronized, $supervisorIsSynchronized));
        $changes = $this->userComparator->getChanges($javeloUserToUpdate, $previousJaveloUser);

        $posterIri = null !== $this->security->getUser()
            ? $this->iriConverter->getIriFromResource($this->security->getUser())
            : null;

        if (null === $previousJaveloUser && $javeloUserToUpdate->active) {
            $this->messageBus->dispatch(new CreateUserMessage(
                $javeloUserToUpdate,
                $changes,
                $posterIri
            ));

            return;
        }

        if ($previousJaveloUser instanceof User && !empty($changes)) {
            $this->messageBus->dispatch(new UpdateUserMessage(
                $javeloUserToUpdate,
                $changes,
                $posterIri
            ));
        }
    }
}

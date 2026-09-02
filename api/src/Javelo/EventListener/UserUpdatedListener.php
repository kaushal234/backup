<?php

declare(strict_types=1);

namespace App\Javelo\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Javelo\Event\UserUpdatedEvent;
use App\Javelo\Repository\UserClientRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class UserUpdatedListener
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly UserClientRepository $userClientRepository,
        private readonly LoggerInterface $javeloRequestLogger,
    ) {
    }

    public function __invoke(UserUpdatedEvent $event): void
    {
        $poster = null !== $event->getPosterIri()
            ? $this->iriConverter->getResourceFromIri($event->getPosterIri())
            : null;

        try {
            $this->userClientRepository->updateUser($event->getJaveloUser(), $event->getChanges(), $poster);
        } catch (\Exception $exception) {
            $this->javeloRequestLogger->error('Something went wrong when updating {userName} on Javelo: {error}', [
                'userName' => $event->getJaveloUser()->userName,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}

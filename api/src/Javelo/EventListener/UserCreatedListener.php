<?php

declare(strict_types=1);

namespace App\Javelo\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Javelo\Event\UserCreatedEvent;
use App\Javelo\Repository\UserClientRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class UserCreatedListener
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly UserClientRepository $userClientRepository,
        private readonly LoggerInterface $javeloRequestLogger,
    ) {
    }

    public function __invoke(UserCreatedEvent $event): void
    {
        /** @var People $poster */
        $poster = null !== $event->getPosterIri()
            ? $this->iriConverter->getResourceFromIri($event->getPosterIri())
            : null;

        try {
            $this->userClientRepository->createUser($event->getJaveloUser(), $event->getChanges(), $poster);
        } catch (\Exception $exception) {
            $this->javeloRequestLogger->error('Something went wrong when creating {userName} on Javelo: {error}', [
                'userName' => (string) $event->getJaveloUser()->userName,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}

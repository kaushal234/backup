<?php

declare(strict_types=1);

namespace App\Agile\MessageHandler;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Agile\Message\UpdateUserMessage;
use App\Agile\UserSyncProcessor;
use App\Entity\Directory\People;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateUserMessageHandler
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly UserSyncProcessor $userSyncProcessor,
    ) {
    }

    public function __invoke(UpdateUserMessage $message): void
    {
        /** @var People $people */
        $people = $this->iriConverter->getResourceFromIri($message->peopleIri);
        $context = $this->userSyncProcessor->resolveSyncContext($people);

        if (null === $context) {
            return;
        }

        $this->userSyncProcessor->executeSync($context);
    }
}

<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\User;

use App\CQRS\Command\User\SendPasswordEmailCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Security\Security;
use App\Security\User\UserProvider;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[AsMessageHandler(bus: 'command.bus')]
class SendPasswordEmailCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserProvider $provider,
        private readonly Security $security
    ) {
    }

    public function __invoke(SendPasswordEmailCommand $command): void
    {
        $user = $this->security->getAuthenticatedUser();
        if ($user instanceof PasswordAuthenticatedUserInterface) {
            $this->provider->sendPasswordConfirmationEmail($user);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\User;

use App\CQRS\Command\User\UpdatePasswordCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Security\Security;
use App\Security\User\UserProvider;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[AsMessageHandler(bus: 'command.bus')]
final class UpdatePasswordCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserProvider $userProvider,
    ) {
    }

    public function __invoke(UpdatePasswordCommand $message): void
    {
        $user = $this->security->getAuthenticatedUser();
        if ($user instanceof PasswordAuthenticatedUserInterface) {
            $this->userProvider->updatePassword($user, $message->password, $message->token);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\Settings;

use App\CQRS\Command\Settings\ChangePasswordCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Security\Security;
use App\Security\User\UserProvider;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class ChangePasswordCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserProvider $userProvider,
    ) {
    }

    public function __invoke(ChangePasswordCommand $message): void
    {
        $user = $this->security->getAuthenticatedUser();
        if ($user instanceof PasswordAuthenticatedUserInterface) {
            $this->userProvider->upgradePassword($user, $message->password);
        }
    }
}

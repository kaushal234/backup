<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\Security\PasswordReset;

use App\CQRS\Command\Security\PasswordReset\CheckPasswordResetTokenCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\Http\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
class CheckPasswordResetTokenCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(CheckPasswordResetTokenCommand $command): void
    {
        $this->client->request(Request::METHOD_GET, \sprintf('/reset_password_confirmation/%s/%s', $command->id, $command->token));
    }
}

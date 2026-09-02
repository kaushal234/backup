<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\Security\PasswordReset;

use App\CQRS\Command\Security\PasswordReset\ConfirmPasswordResetCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request;

use function sprintf;

final class ConfirmPasswordResetCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function __invoke(ConfirmPasswordResetCommand $command): void
    {
        $this->client->request(
            Request::METHOD_POST,
            sprintf('/reset_password_confirmation/%s/%s', $command->id, $command->token),
            ['json' => ['newPassword' => $command->newPassword]],
        );
    }
}

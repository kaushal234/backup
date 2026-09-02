<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\Security\PasswordReset;

use App\CQRS\Command\Security\PasswordReset\SendPasswordResetEmailCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request;

final class SendPasswordResetEmailCommandHandler implements CommandHandlerInterface
{
    private const PORTAL = 'evendors';

    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function __invoke(SendPasswordResetEmailCommand $command): void
    {
        $this->client->request(Request::METHOD_POST, '/reset_password', [
            'json' => [
                'portal' => self::PORTAL,
                'email' => $command->email,
            ],
        ]);
    }
}

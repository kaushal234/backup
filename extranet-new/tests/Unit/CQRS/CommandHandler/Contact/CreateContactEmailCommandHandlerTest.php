<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\CommandHandler\Contact;

use App\CQRS\Command\Contact\ContactEmailCommand;
use App\CQRS\CommandHandler\Contact\CreateContactEmailCommandHandler;
use App\DataTransferObject\Contact\ContactEmail;
use App\Sdk\Client;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
final class CreateContactEmailCommandHandlerTest extends TestCase
{
    public function testInvocation(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('create')->with(ContactEmail::class, [
            'message' => 'foo',
            'to' => 'foo@bar.com',
        ]);

        $command = new ContactEmailCommand('foo', 'foo@bar.com');
        $handler = new CreateContactEmailCommandHandler($client);
        $handler->__invoke($command);
    }
}

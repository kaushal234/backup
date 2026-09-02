<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\CommandHandler\TechnicianOnCall;

use App\CQRS\Command\TechnicianOnCall\AddFileTechnicianOnCallCommand;
use App\CQRS\CommandHandler\TechnicianOnCall\AddFileTechnicianOnCallCommandHandler;
use App\Sdk\Client;
use App\Sdk\Resource\TechnicianOnCall;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @group unit
 */
final class AddFileTechnicianOnCallCommandHandlerTest extends TestCase
{
    public function testInvocation(): void
    {
        $client = $this->createMock(Client::class);
        $command = new AddFileTechnicianOnCallCommand(
            id: 42,
            file: new UploadedFile(__DIR__.'/../../../../Fixtures/Files/image.gif', 'image.gif'),
            description: 'Test technician'
        );
        $client->expects($this->once())->method('upload')->with(TechnicianOnCall::class, $command);

        $handler = new AddFileTechnicianOnCallCommandHandler($client);
        $handler->__invoke($command);
    }
}

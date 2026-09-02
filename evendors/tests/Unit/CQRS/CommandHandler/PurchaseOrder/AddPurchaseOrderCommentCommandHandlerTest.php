<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\CommandHandler\PurchaseOrder;

use App\CQRS\Command\PurchaseOrder\AddPurchaseOrderCommentCommand;
use App\CQRS\CommandHandler\PurchaseOrder\AddPurchaseOrderCommentCommandHandler;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\PurchaseOrder;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
final class AddPurchaseOrderCommentCommandHandlerTest extends TestCase
{
    public function testInvocation(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('update')->with(PurchaseOrder::class, ['iri' => '/foo/1'], [
            'message' => 'hello',
        ]);

        $handler = new AddPurchaseOrderCommentCommandHandler($client);
        $command = new AddPurchaseOrderCommentCommand('/foo/1', 'hello');
        $handler->__invoke($command);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\CommandHandler\PurchaseOrder;

use App\CQRS\Command\PurchaseOrder\EditPurchaseOrderAllLineCommand;
use App\CQRS\CommandHandler\PurchaseOrder\EditPurchaseOrderAllLineCommandHandler;
use App\DataTransferObject\PurchaseOrder\EditAllPurchaseOrderLine;
use App\DataTransferObject\PurchaseOrder\EditPurchaseOrderLine;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\PurchaseOrder;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
final class EditPurchaseOrderAllLineCommandHandlerTest extends TestCase
{
    public function testInvocation(): void
    {
        $editPurchaseOrderLine = new EditPurchaseOrderLine();
        $editPurchaseOrderLine->lineIdentifier = '1';
        $editPurchaseOrderLine->sequence = 1;
        $editPurchaseOrderLine->isConfirmable = true;
        $editPurchaseOrderLine->setConfirmedSupplierDate(new DateTimeImmutable('2055-12-01'));

        $editAllPurchaseOrderLine = new EditAllPurchaseOrderLine();
        $editAllPurchaseOrderLine->iri = '/ion/purchase_orders/1';
        $editAllPurchaseOrderLine->addEditLine($editPurchaseOrderLine);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('update')->with(PurchaseOrder::class, ['iri' => '/ion/purchase_orders/1']);

        $handler = new EditPurchaseOrderAllLineCommandHandler($client);
        $command = new EditPurchaseOrderAllLineCommand($editAllPurchaseOrderLine);

        $handler->__invoke($command);
    }
}

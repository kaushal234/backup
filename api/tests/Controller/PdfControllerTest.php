<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use App\Controller\PdfController;
use App\Formatter\Snappy\FormatterInterface;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Dto\Procurement\Orders\PurchaseOrderPdfInput;
use App\ION\Dto\Procurement\Orders\PurchaseOrderPdfLineInput;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use App\ION\Resources\Procurement\Orders\PurchaseOrderLine;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PdfControllerTest extends TestCase
{
    use ProphecyTrait;
    private ObjectProphecy $pdfFormatter;
    private ObjectProphecy $resourceMetadataCollectionFactory;
    private ObjectProphecy $itemDataProvider;
    private ObjectProphecy $security;
    private PdfController $controller;

    protected function setUp(): void
    {
        $this->pdfFormatter = $this->prophesize(FormatterInterface::class);
        $this->resourceMetadataCollectionFactory = $this->prophesize(ResourceMetadataCollectionFactoryInterface::class);
        $this->itemDataProvider = $this->prophesize(CachedIONItemDataProvider::class);
        $this->security = $this->prophesize(Security::class);

        $this->controller = new PdfController(
            $this->pdfFormatter->reveal(),
            $this->resourceMetadataCollectionFactory->reveal(),
            $this->itemDataProvider->reveal(),
            $this->security->reveal(),
        );
    }

    public function testNoDataToPrint(): void
    {
        $operation = new Get(class: PurchaseOrder::class);
        $this->resourceMetadataCollectionFactory->create(PurchaseOrder::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(PurchaseOrder::class, [new ApiResource(operations: [$operation])])
        );

        $purchaseOrder = new PurchaseOrder();

        $this->itemDataProvider->provide($operation, ['orderIdentifier' => 'bar'])->shouldBeCalledOnce()->willReturn($purchaseOrder);
        $this->security->isGranted('ACCESS_PEOPLE')->shouldBeCalledOnce()->willReturn(true);

        $this->expectException(BadRequestHttpException::class);

        $this->controller->__invoke(new PurchaseOrderPdfInput(), 'bar', 'foo', new Request());
    }

    public function testWithOneLine(): void
    {
        $purchaseOrderPdfInput = new PurchaseOrderPdfInput();

        $purchaseOrderPdfLineInput = new PurchaseOrderPdfLineInput();
        $purchaseOrderPdfLineInput->lineIdentifier = '3';
        $purchaseOrderPdfLineInput->sequence = 1;
        $purchaseOrderPdfLineInput->quantityLabel = 2;
        $purchaseOrderPdfLineInput->packingSlip = '123';
        $purchaseOrderPdfLineInput->labelDeliveredQuantity = 14;
        $purchaseOrderPdfInput->addLine($purchaseOrderPdfLineInput);

        $operation = new Get(class: PurchaseOrder::class);
        $this->resourceMetadataCollectionFactory->create(PurchaseOrder::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(PurchaseOrder::class, [new ApiResource(operations: [$operation])])
        );

        $this->itemDataProvider->provide($operation, ['orderIdentifier' => 'bar'])->shouldBeCalledOnce()->willReturn($purchaseOrder = $this->getDefaultPurchaseOrder());

        $this->security->isGranted('ACCESS_PEOPLE')->shouldBeCalledOnce()->willReturn(true);
        $this->pdfFormatter->convert($purchaseOrder, 'foo', 'pdf');

        $this->controller->__invoke($purchaseOrderPdfInput, 'bar', 'foo', new Request());

        $this->assertCount(1, $purchaseOrder->getLines(), 'should have only 1 row because transform remove the other 3 lines');
        $line = current($purchaseOrder->getLines());
        $this->assertSame($line->lineIdentifier, '3');
        $this->assertSame($line->quantityLabel, 2.0);
        $this->assertSame($line->labelDeliveredQuantity, 14);
    }

    public function testWithAllLines(): void
    {
        $purchaseOrderPdfInput = new PurchaseOrderPdfInput();

        for ($i = 1; $i <= 4; ++$i) {
            $purchaseOrderPdfLineInput = new PurchaseOrderPdfLineInput();
            $purchaseOrderPdfLineInput->lineIdentifier = (string) $i;
            $purchaseOrderPdfLineInput->sequence = 1;
            $purchaseOrderPdfLineInput->quantityLabel = 2;
            $purchaseOrderPdfLineInput->packingSlip = '123';
            $purchaseOrderPdfLineInput->labelDeliveredQuantity = 14;
            $purchaseOrderPdfInput->addLine($purchaseOrderPdfLineInput);
        }

        $operation = new Get(class: PurchaseOrder::class);
        $this->resourceMetadataCollectionFactory->create(PurchaseOrder::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(PurchaseOrder::class, [new ApiResource(operations: [$operation])])
        );

        $this->itemDataProvider->provide($operation, ['orderIdentifier' => 'bar'])->shouldBeCalledOnce()->willReturn($purchaseOrder = $this->getDefaultPurchaseOrder());

        $this->security->isGranted('ACCESS_PEOPLE')->shouldBeCalledOnce()->willReturn(false);
        $this->security->isGranted('BUSINESS_PARTNER_VOTER', $purchaseOrder)->shouldBeCalledOnce()->willReturn(true);

        $this->pdfFormatter->convert($purchaseOrder, 'foo', 'pdf');

        $this->controller->__invoke($purchaseOrderPdfInput, 'bar', 'foo', new Request());

        $this->assertCount(3, $purchaseOrder->getLines());

        $line = $purchaseOrder->getLines()[1];
        $this->assertSame($line->lineIdentifier, '3', 'test second row');
        $this->assertSame($line->quantityLabel, 2.0);
        $this->assertSame($line->labelDeliveredQuantity, 14);

        $line = $purchaseOrder->getLines()[2];
        $this->assertSame($line->lineIdentifier, '4', 'test last row');
        $this->assertSame($line->quantityLabel, 2.0);
        $this->assertSame($line->labelDeliveredQuantity, 14);
    }

    public function testWithNotFoundPurchaseOrderLine(): void
    {
        $this->expectException(BadRequestException::class);

        $purchaseOrderPdfInput = new PurchaseOrderPdfInput();
        $purchaseOrderPdfLineInput = new PurchaseOrderPdfLineInput();
        $purchaseOrderPdfLineInput->lineIdentifier = '5';
        $purchaseOrderPdfLineInput->sequence = 1;
        $purchaseOrderPdfLineInput->packingSlip = '123';
        $purchaseOrderPdfLineInput->labelDeliveredQuantity = 14;
        $purchaseOrderPdfInput->addLine($purchaseOrderPdfLineInput);

        $operation = new Get(class: PurchaseOrder::class);
        $this->resourceMetadataCollectionFactory->create(PurchaseOrder::class)->shouldBeCalledOnce()->willReturn(
            new ResourceMetadataCollection(PurchaseOrder::class, [new ApiResource(operations: [$operation])])
        );

        $this->itemDataProvider->provide($operation, ['orderIdentifier' => 'bar'])->shouldBeCalledOnce()->willReturn($purchaseOrder = $this->getDefaultPurchaseOrder());

        $this->security->isGranted('ACCESS_PEOPLE')->shouldBeCalledOnce()->willReturn(true);

        $this->controller->__invoke($purchaseOrderPdfInput, 'bar', 'foo', new Request());
    }

    protected function getDefaultPurchaseOrder(): PurchaseOrder
    {
        $purchaseOrder = new PurchaseOrder();

        $purchaseOrderLine = new PurchaseOrderLine();
        $purchaseOrderLine->lineIdentifier = '1';
        $purchaseOrderLine->sequence = 1;
        $purchaseOrderLine->isConfirmable = true;
        $purchaseOrder->addLine($purchaseOrderLine);

        $purchaseOrderLine = new PurchaseOrderLine();
        $purchaseOrderLine->lineIdentifier = '2';
        $purchaseOrderLine->sequence = 1;
        $purchaseOrderLine->isConfirmable = false;
        $purchaseOrder->addLine($purchaseOrderLine);

        $purchaseOrderLine = new PurchaseOrderLine();
        $purchaseOrderLine->lineIdentifier = '3';
        $purchaseOrderLine->sequence = 1;
        $purchaseOrderLine->isConfirmable = true;
        $purchaseOrder->addLine($purchaseOrderLine);

        $purchaseOrderLine = new PurchaseOrderLine();
        $purchaseOrderLine->lineIdentifier = '4';
        $purchaseOrderLine->sequence = 1;
        $purchaseOrderLine->isConfirmable = true;
        $purchaseOrder->addLine($purchaseOrderLine);

        $purchaseOrder->orderIdentifier = 'bar';
        $purchaseOrder->purchaseOfficeCode = 'foobar';

        return $purchaseOrder;
    }
}

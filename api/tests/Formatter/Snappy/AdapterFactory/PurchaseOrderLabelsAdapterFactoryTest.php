<?php

declare(strict_types=1);

namespace App\Tests\Formatter\Snappy\AdapterFactory;

use App\Formatter\Snappy\Adapter;
use App\Formatter\Snappy\AdapterFactory\PurchaseOrderLabelsAdapterFactory;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PurchaseOrderLabelsAdapterFactoryTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAdapterReturnsAPurchaseOrderLabelsAdapter()
    {
        /** @var PurchaseOrderLabelsAdapterFactory $purchaseOrderLabelsAdapterFactory */
        $purchaseOrderLabelsAdapterFactory = static::getContainer()->get(PurchaseOrderLabelsAdapterFactory::class);
        self::assertInstanceOf(
            Adapter::class,
            $purchaseOrderLabelsAdapterFactory->getAdapter(new PurchaseOrder(), 'pdf'),
            \sprintf('the class returned should be %s', Adapter::class)
        );
    }

    public function testGetTemplateInPdfAdapterIsNotEmpty()
    {
        /** @var PurchaseOrderLabelsAdapterFactory $purchaseOrderLabelsAdapterFactory */
        $purchaseOrderLabelsAdapterFactory = static::getContainer()->get(PurchaseOrderLabelsAdapterFactory::class);
        $adapter = $purchaseOrderLabelsAdapterFactory->getAdapter(new PurchaseOrder(), 'pdf');

        $template = 'Pdf/PurchaseOrder/labels_layout.html.twig';
        self::assertSame(
            $template,
            $adapter->getTemplate(),
            \sprintf('Template should be "%s"', $template)
        );
    }

    public function testGetContextInPdfAdapterIsValid()
    {
        $purchaseOrder = new PurchaseOrder();
        /** @var PurchaseOrderLabelsAdapterFactory $purchaseOrderLabelsAdapterFactory */
        $purchaseOrderLabelsAdapterFactory = static::getContainer()->get(PurchaseOrderLabelsAdapterFactory::class);
        $adapter = $purchaseOrderLabelsAdapterFactory->getAdapter($purchaseOrder, 'pdf');

        $context = $adapter->getContext();

        self::assertArrayHasKey(
            'purchaseOrder',
            $context,
            "The context should have a 'purchaseOrder' key"
        );

        self::assertSame(
            $purchaseOrder,
            $context['purchaseOrder'],
            "The purchaseOrder in context doesn't return the original purchaseOrder"
        );
    }

    public function testGetSupported()
    {
        /** @var PurchaseOrderLabelsAdapterFactory $purchaseOrderLabelsAdapterFactory */
        $purchaseOrderLabelsAdapterFactory = static::getContainer()->get(PurchaseOrderLabelsAdapterFactory::class);
        self::assertTrue(
            $purchaseOrderLabelsAdapterFactory->supports(new PurchaseOrder(), 'pdf'),
            \sprintf('%s should be supported', Adapter::class)
        );
    }
}

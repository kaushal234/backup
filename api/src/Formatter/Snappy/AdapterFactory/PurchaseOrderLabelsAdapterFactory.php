<?php

declare(strict_types=1);

namespace App\Formatter\Snappy\AdapterFactory;

use App\Formatter\Snappy\Adapter;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;

class PurchaseOrderLabelsAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @var string
     */
    final public const PURPOSE = 'purchase_order_labels';

    /**
     * {@inheritdoc}
     */
    public function getAdapter($purchaseOrder, string $format): Adapter
    {
        $adapter = new Adapter(
            'Pdf/PurchaseOrder/labels_layout.html.twig',
            ['purchaseOrder' => $purchaseOrder]
        );
        $adapter
            ->addOption('disable-smart-shrinking', true)
            ->addOption('margin-top', 8)
            ->addOption('margin-left', 1.2)
            ->addOption('margin-right', 1.2)
            ->addOption('margin-bottom', 4)
        ;

        return $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $format): bool
    {
        return $object instanceof PurchaseOrder && 'pdf' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getPurpose(): string
    {
        return self::PURPOSE;
    }
}

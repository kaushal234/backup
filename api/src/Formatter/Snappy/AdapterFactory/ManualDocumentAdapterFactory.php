<?php

declare(strict_types=1);

namespace App\Formatter\Snappy\AdapterFactory;

use App\Entity\Support\ManualDocument;
use App\Formatter\Snappy\Adapter;

class ManualDocumentAdapterFactory extends ItemAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @var string
     */
    final public const PURPOSE = 'pdf_document';

    /**
     * @param ManualDocument $manualDocument
     */
    public function getAdapter($manualDocument, string $format): Adapter
    {
        return $this->process($manualDocument->manual, $manualDocument);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $format): bool
    {
        return $object instanceof ManualDocument && self::PURPOSE === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getPurpose(): string
    {
        return self::PURPOSE;
    }
}

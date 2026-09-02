<?php

declare(strict_types=1);

namespace App\Formatter\Snappy\AdapterFactory;

use App\Entity\Support\Manual;
use App\Formatter\Snappy\Adapter;

class ManualProductNumberListAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @var string
     */
    final public const PURPOSE = 'pdf_parts_list';

    /**
     * {@inheritdoc}
     */
    public function getAdapter($manual, string $format): Adapter
    {
        $partsData = [];

        foreach ($manual->getDocuments() as $document) {
            foreach ($document->getParts() as $part) {
                $partsData[] = ['partNumber' => $part->partNumber, 'document' => $part->document->factoryNumber, 'position' => $part->position];
            }
        }
        usort($partsData, static function ($a, $b) {
            return $a['partNumber'] <=> $b['partNumber'];
        });

        $adapter = new Adapter(
            'Pdf/Support/Manual/ProductNumberList/layout.html.twig',
            ['manual' => $manual, 'partsData' => $partsData]
        );
        $adapter->setHeaderTemplate('Pdf/Support/Manual/ProductNumberList/header.html.twig');
        $adapter->setFooterTemplate('Pdf/Support/Manual/footer.html.twig');

        return $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $format): bool
    {
        return $object instanceof Manual && self::PURPOSE === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getPurpose(): string
    {
        return self::PURPOSE;
    }
}

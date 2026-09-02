<?php

declare(strict_types=1);

namespace App\Formatter\Snappy\AdapterFactory;

use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use App\Formatter\Snappy\Adapter;

abstract class ItemAdapterFactory
{
    public function process(Manual $manual, ManualDocument $document, array $options = [])
    {
        $adapter = new Adapter(
            'Pdf/Support/Manual/Document/layout.html.twig',
            ['manual' => $manual, 'document' => $document] + $options,
        );
        $adapter->setHeaderTemplate('Pdf/Support/Manual/Document/header.html.twig');
        $adapter->setFooterTemplate('Pdf/Support/Manual/footer.html.twig');

        // without this option, you can not read local pdf/jpeg files that will be included in the pdf
        $adapter->addOption('enable-local-file-access', true);

        return $adapter;
    }
}

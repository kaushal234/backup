<?php

declare(strict_types=1);

namespace App\Controller\Manufacturing;

use App\Formatter\Snappy\AdapterFactory\BillOfMaterialsAdapterFactory;
use App\Formatter\Snappy\FormatterInterface;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterialItem;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

class BillOfMaterialsPdfController
{
    private FormatterInterface $pdfFormatter;

    public function __construct(FormatterInterface $pdfFormatter)
    {
        $this->pdfFormatter = $pdfFormatter;
    }

    public function __invoke(BillOfMaterialItem $data): PdfResponse
    {
        $content = $this->pdfFormatter->convert($data, BillOfMaterialsAdapterFactory::PURPOSE, BillOfMaterialsAdapterFactory::PURPOSE);

        return new PdfResponse($content, \sprintf('pdf-testing-%07s.pdf', $data->getPartNumber()), 'application/pdf', 'inline');
    }
}

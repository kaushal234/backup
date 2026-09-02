<?php

declare(strict_types=1);

namespace App\Controller\Manufacturing;

use App\Formatter\Snappy\AdapterFactory\BOMShopfloorAdapterFactory;
use App\Formatter\Snappy\FormatterInterface;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\ShopfloorPdf;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

class BOMShopfloorPdfController
{
    private FormatterInterface $pdfFormatter;

    public function __construct(FormatterInterface $pdfFormatter)
    {
        $this->pdfFormatter = $pdfFormatter;
    }

    public function __invoke(ShopfloorPdf $data): PdfResponse
    {
        $content = $this->pdfFormatter->convert($data, BOMShopfloorAdapterFactory::PURPOSE, BOMShopfloorAdapterFactory::PURPOSE);

        return new PdfResponse($content, \sprintf('pdf-testing-%07s.pdf', $data->product), 'application/pdf', 'inline');
    }
}

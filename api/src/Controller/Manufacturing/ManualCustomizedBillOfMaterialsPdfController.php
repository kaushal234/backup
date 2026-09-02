<?php

declare(strict_types=1);

namespace App\Controller\Manufacturing;

use App\Factory\Support\ManualFactory;
use App\Formatter\Snappy\AdapterFactory\ManualChapter4ContentAdapterFactory;
use App\Formatter\Snappy\FormatterInterface;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Manuals;
use App\ION\Resources\Manufacturing\JobShop\ManualCustomizedBillOfMaterials;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

class ManualCustomizedBillOfMaterialsPdfController
{
    private FormatterInterface $pdfFormatter;
    private ManualFactory $manualFactory;

    public function __construct(FormatterInterface $pdfFormatter, ManualFactory $manualFactory)
    {
        $this->pdfFormatter = $pdfFormatter;
        $this->manualFactory = $manualFactory;
    }

    public function __invoke(Manuals|ManualCustomizedBillOfMaterials $data): PdfResponse
    {
        $manual = $this->manualFactory->createFromManualCustomizedBillOfMaterials($data);
        $manual->language = 'ENGLISH';

        $content = $this->pdfFormatter->convert($manual, ManualChapter4ContentAdapterFactory::PURPOSE, ManualChapter4ContentAdapterFactory::PURPOSE, ['manual_locale' => 'en']);

        return new PdfResponse($content, \sprintf('parts-book-%07s.pdf', $data->project), 'application/pdf', 'inline');
    }
}

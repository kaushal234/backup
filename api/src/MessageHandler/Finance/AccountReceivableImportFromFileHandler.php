<?php

declare(strict_types=1);

namespace App\MessageHandler\Finance;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Manager\Finance\AccountReceivableManager;
use App\Message\Finance\AccountReceivableImportFromFile;
use App\Serializer\XlsxReader;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AccountReceivableImportFromFileHandler
{
    private readonly XlsxReader $xlsxReader;
    private readonly AccountReceivableManager $manager;
    private readonly IriConverterInterface $iriConverter;

    public function __construct(XlsxReader $xlsxReader, AccountReceivableManager $manager, IriConverterInterface $iriConverter)
    {
        $this->xlsxReader = $xlsxReader;
        $this->manager = $manager;
        $this->iriConverter = $iriConverter;
    }

    public function __invoke(AccountReceivableImportFromFile $message)
    {
        $propertyConversion = [
            'ERP company' => 'erp',
            'Customer Name' => 'customerName',
            'Customer PO#' => 'purchaseOrderNumber',
            'Org Inv Amt' => 'originalAmount',
            'Org Inv Amt(local currency)' => 'originalAmountLocalCurrency',
            'Bal Inv Amt' => 'balanceAmount',
            'Bal Inv Amt(local currency)' => 'balanceAmountLocalCurrency',
            'Transaction Type' => 'transactionType',
            'Customer Code' => 'pcust',
            'Currency' => 'currency',
            'Customer Country' => 'country',
        ];

        $stringConversion = [
            'ERP Inv#' => 'erpInvoiceNumber',
            'Inv Ref A' => 'salesReferenceA',
            'Inv Ref B' => 'salesReferenceB',
            'Fin Ref 1' => 'financeReferenceA',
            'Fin Ref 2' => 'financeReferenceB',
        ];

        $dateConversion = [
            'Inv. Date' => 'invoiceDate',
            'Due date' => 'dueDate',
            'Our SO date' => 'salesOrderDate',
        ];

        $intConversion = [
            'Our SO#' => 'salesOrderNumber',
            'Credit Analyst' => 'creditAnalyst',
        ];

        /** @var Location $location */
        $location = $this->iriConverter->getResourceFromIri($message->locationIri);
        $file = new File($message->filepath);
        $this->manager->createAccountReceivableAndSendReport($this->xlsxReader->normalize($file, ['propertyConversion' => $propertyConversion, 'stringConversion' => $stringConversion, 'dateConversion' => $dateConversion, 'intConversion' => $intConversion]), $location, [$message->to]);

        unlink($file->getRealPath());
    }
}

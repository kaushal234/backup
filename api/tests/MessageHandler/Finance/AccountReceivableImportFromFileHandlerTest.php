<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler\Finance;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Manager\Finance\AccountReceivableManager;
use App\Message\Finance\AccountReceivableImportFromFile;
use App\MessageHandler\Finance\AccountReceivableImportFromFileHandler;
use App\Serializer\XlsxReader;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\File;

class AccountReceivableImportFromFileHandlerTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testHandler(): void
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $accountReceivableManagerProphecy = $this->prophesize(AccountReceivableManager::class);

        $file = new File('tests/fixtures/ar_test.xlsx');
        file_put_contents('tests/fixtures/ar_test_to_remove.xlsx', file_get_contents($file->getRealPath()));

        $arFromXlsx = [
            'erp' => 540,
            'pcust' => 123456,
            'customerName' => 'Customer Test',
            'country' => 'FR',
            'transactionType' => 'Type-ex',
            'erpInvoiceNumber' => 69696969,
            'purchaseOrderNumber' => '987654',
            'invoiceDate' => '2020-12-25',
            'dueDate' => '2021-01-01',
            'currency' => 'USD',
            'originalAmount' => 250000,
            'originalAmountLocalCurrency' => 270000,
            'balanceAmount' => 150000,
            'balanceAmountLocalCurrency' => 165000,
            'salesOrderNumber' => 456789,
            'salesOrderDate' => '2020-12-25',
            'salesReferenceA' => '69',
            'salesReferenceB' => 'test',
        ];

        $iriConverterProphecy->getResourceFromIri('/locations/27')->shouldBeCalledOnce()->willReturn($location = new Location());
        $accountReceivableManagerProphecy->createAccountReceivableAndSendReport([$arFromXlsx], $location, ['toto@tld.fr'])->shouldBeCalledOnce();

        $message = new AccountReceivableImportFromFile('tests/fixtures/ar_test_to_remove.xlsx', 'toto@tld.fr', '/locations/27');
        $handler = new AccountReceivableImportFromFileHandler(new XlsxReader(), $accountReceivableManagerProphecy->reveal(), $iriConverterProphecy->reveal());

        $handler($message);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Command\Purchasing\VendorWarrantyClaim;

use App\Command\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimAutomaticallyChangedToIssueDebitNote;
use App\Entity\Directory\Location;
use App\Entity\Finance\Currency;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Entity\Purchasing\VendorWarrantyClaimThreshold;
use App\Notifier\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimNotifier;
use App\Repository\Finance\ExchangeRateRepository;
use App\Repository\Purchasing\VendorWarrantyClaimRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class VendorWarrantyClaimAutomaticallyChangedToIssueDebitNoteTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @dataProvider providerStatusChangeWhenRequestedCreditAmountIsGreaterThanThreshold
     */
    public function testStatusChangeWithDifferentCurrencies(
        string $currency,
        string $thresholdCurrency,
        int $requestedCreditAmount,
        int $threshold,
        string $expectedStatus
    ) {
        $vwc = $this->createNCRVendorWarrantyClaim($currency, new \DateTime('-30 days'), $requestedCreditAmount);
        $thresholdEntity = $this->createThreshold($thresholdCurrency, $threshold);

        $statusIssueDebitExpected = new VendorWarrantyClaimStatus();
        $statusIssueDebitExpected->name = $expectedStatus;

        $thresholdRepo = $this->createMock(EntityRepository::class);
        $thresholdRepo->expects($this->once())->method('findOneBy')->willReturn($thresholdEntity);

        $statusRepo = $this->createMock(EntityRepository::class);
        $statusRepo->expects($this->once())->method('findOneBy')->willReturn($statusIssueDebitExpected);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->exactly(2))->method('getRepository')->willReturnMap([
            [VendorWarrantyClaimThreshold::class, $thresholdRepo],
            [VendorWarrantyClaimStatus::class, $statusRepo],
        ]);

        $vwcRepo = $this->createMock(VendorWarrantyClaimRepository::class);
        $vwcRepo->expects($this->once())->method('getVendorToResponsesExpired')->willReturn([$vwc]);

        $exchangeRateRepo = $this->createMock(ExchangeRateRepository::class);
        $exchangeRateRepo->expects($this->exactly(2))
            ->method('convertAmount')
            ->willReturnCallback(static fn ($amount) => $amount);

        $notifier = $this->createMock(VendorWarrantyClaimNotifier::class);
        $notifier->expects($this->once())
            ->method('sendStatus')
            ->with(
                $this->isInstanceOf(VendorWarrantyClaim::class),
                VendorWarrantyClaimStatus::VENDOR_TO_RESPOND
            );

        $command = new VendorWarrantyClaimAutomaticallyChangedToIssueDebitNote(
            $em,
            $vwcRepo,
            $exchangeRateRepo,
            $notifier
        );

        $tester = $this->executeCommand($command);
        $this->assertSame(0, $tester->getStatusCode());
        $this->assertSame($statusIssueDebitExpected, $vwc->status);
    }

    public function providerStatusChangeWhenRequestedCreditAmountIsGreaterThanThreshold(): array
    {
        return [
            'EUR threshold, EUR NCR: requestedCreditAmount > threshold' => [
                'currency' => 'EUR',
                'thresholdCurrency' => 'EUR',
                'requestedCreditAmount' => 2000,
                'threshold' => 150,
                'expectedStatus' => VendorWarrantyClaimStatus::ISSUE_DEBIT_NOTE,
            ],
            'CNY threshold, EUR NCR: requestedCreditAmount > threshold' => [
                'currency' => 'EUR',
                'thresholdCurrency' => 'CNY',
                'requestedCreditAmount' => 15000,
                'threshold' => 1000,
                'expectedStatus' => VendorWarrantyClaimStatus::ISSUE_DEBIT_NOTE,
            ],
        ];
    }

    /**
     * @dataProvider providerNoChangesNoEmailsWhenRequestedCreditAmountIsLowerThanThreshold
     */
    public function testNoChangesNoEmailsWhenRequestedCreditAmountIsLowerThanThreshold(
        string $currency,
        string $thresholdCurrency,
        int $requestedCreditAmount,
        int $threshold,
        string $expectedStatus
    ) {
        $vwc = $this->createNCRVendorWarrantyClaim($currency, new \DateTime('-30 days'), $requestedCreditAmount);
        $thresholdEntity = $this->createThreshold($thresholdCurrency, $threshold);

        $statusVendorToRespondExpected = new VendorWarrantyClaimStatus();
        $statusVendorToRespondExpected->name = $expectedStatus;

        $thresholdRepo = $this->createMock(EntityRepository::class);
        $thresholdRepo->expects($this->once())->method('findOneBy')->willReturn($thresholdEntity);

        $statusRepo = $this->createMock(EntityRepository::class);
        $statusRepo->expects($this->once())->method('findOneBy')->willReturn($statusVendorToRespondExpected);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->exactly(2))->method('getRepository')->willReturnMap([
            [VendorWarrantyClaimThreshold::class, $thresholdRepo],
            [VendorWarrantyClaimStatus::class, $statusRepo],
        ]);

        $vwcRepo = $this->createMock(VendorWarrantyClaimRepository::class);
        $vwcRepo->expects($this->once())->method('getVendorToResponsesExpired')->willReturn([$vwc]);

        $exchangeRateRepo = $this->createMock(ExchangeRateRepository::class);
        $exchangeRateRepo->expects($this->exactly(2))
            ->method('convertAmount')
            ->willReturnCallback(static fn ($amount) => $amount);

        $notifier = $this->createMock(VendorWarrantyClaimNotifier::class);
        $notifier->expects($this->never())
            ->method('sendStatus');

        $command = new VendorWarrantyClaimAutomaticallyChangedToIssueDebitNote(
            $em,
            $vwcRepo,
            $exchangeRateRepo,
            $notifier
        );

        $tester = $this->executeCommand($command);
        $this->assertSame(0, $tester->getStatusCode());
        $this->assertSame($statusVendorToRespondExpected->name, $vwc->status->name);
    }

    public function providerNoChangesNoEmailsWhenRequestedCreditAmountIsLowerThanThreshold(): array
    {
        return [
            'EUR threshold, EUR NCR: requestedCreditAmount < threshold' => [
                'currency' => 'EUR',
                'thresholdCurrency' => 'EUR',
                'requestedCreditAmount' => 100,
                'threshold' => 150,
                'expectedStatus' => VendorWarrantyClaimStatus::VENDOR_TO_RESPOND,
            ],
            'USD threshold, EUR NCR: requestedCreditAmount < threshold' => [
                'currency' => 'EUR',
                'thresholdCurrency' => 'USD',
                'requestedCreditAmount' => 50,
                'threshold' => 200,
                'expectedStatus' => VendorWarrantyClaimStatus::VENDOR_TO_RESPOND,
            ],
        ];
    }

    /**
     * @dataProvider providerReminderSentWhenDelayIsReached
     */
    public function testReminderSentWhenDelayIsReached(
        string $currency,
        string $thresholdCurrency,
        int $requestedCreditAmount,
        int $threshold,
        string $expectedStatus
    ) {
        $vwc = $this->createNCRVendorWarrantyClaim($currency, new \DateTime('-25 days'), $requestedCreditAmount);
        $thresholdEntity = $this->createThreshold($thresholdCurrency, $threshold);

        $thresholdRepo = $this->createMock(EntityRepository::class);
        $thresholdRepo->expects($this->once())->method('findOneBy')->willReturn($thresholdEntity);

        $statusVendorToRespondExpected = new VendorWarrantyClaimStatus();
        $statusVendorToRespondExpected->name = $expectedStatus;

        $statusRepo = $this->createMock(EntityRepository::class);
        $statusRepo->expects($this->once())->method('findOneBy')->willReturn($statusVendorToRespondExpected);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->exactly(2))->method('getRepository')->willReturnMap([
            [VendorWarrantyClaimThreshold::class, $thresholdRepo],
            [VendorWarrantyClaimStatus::class, $statusRepo],
        ]);

        $vwcRepo = $this->createMock(VendorWarrantyClaimRepository::class);
        $vwcRepo->expects($this->once())->method('getVendorToResponsesExpired')->willReturn([$vwc]);

        $exchangeRateRepo = $this->createMock(ExchangeRateRepository::class);
        $exchangeRateRepo->expects($this->exactly(2))
            ->method('convertAmount')
            ->willReturnCallback(static fn ($amount) => $amount);

        $notifier = $this->createMock(VendorWarrantyClaimNotifier::class);
        $notifier->expects($this->once())
            ->method('sendVendorToResponseReminder')
            ->with($this->isInstanceOf(VendorWarrantyClaim::class));

        $notifier->expects($this->never())->method('sendStatus');

        $command = new VendorWarrantyClaimAutomaticallyChangedToIssueDebitNote(
            $em,
            $vwcRepo,
            $exchangeRateRepo,
            $notifier
        );

        $tester = $this->executeCommand($command);
        $this->assertSame(0, $tester->getStatusCode());
        $this->assertSame($expectedStatus, $vwc->status->name);
    }

    public function providerReminderSentWhenDelayIsReached(): array
    {
        return [
            'EUR threshold, EUR NCR: reminder sent but no status change' => [
                'currency' => 'EUR',
                'thresholdCurrency' => 'EUR',
                'requestedCreditAmount' => 2000,
                'threshold' => 1000,
                'expectedStatus' => VendorWarrantyClaimStatus::VENDOR_TO_RESPOND,
            ],
            'USD threshold, EUR NCR: reminder sent but no status change' => [
                'currency' => 'EUR',
                'thresholdCurrency' => 'USD',
                'requestedCreditAmount' => 300,
                'threshold' => 200,
                'expectedStatus' => VendorWarrantyClaimStatus::VENDOR_TO_RESPOND,
            ],
        ];
    }

    private function createThreshold(string $currencyName, int $amount): VendorWarrantyClaimThreshold
    {
        $currency = new Currency();
        $currency->setName($currencyName);

        $location = new Location();
        $location->setCurrency($currency);

        $threshold = new VendorWarrantyClaimThreshold();
        $threshold->threshold = $amount;
        $threshold->location = $location;

        return $threshold;
    }

    private function createNCRVendorWarrantyClaim(
        string $currencyName,
        \DateTime $vendorToRespondAt,
        int $requestedCreditAmount
    ): NCRVendorWarrantyClaim {
        $currency = new Currency();
        $currency->setName($currencyName);

        $location = new Location();
        $location->setCurrency($currency);

        $vwc = new NCRVendorWarrantyClaim();
        $vwc->vendorToRespondAt = $vendorToRespondAt;
        $vwc->requestedCreditAmount = $requestedCreditAmount;
        $vwc->currency = $currency;
        $vwc->location = $location;
        $vwcStatus = new VendorWarrantyClaimStatus();
        $vwcStatus->name = VendorWarrantyClaimStatus::VENDOR_TO_RESPOND;
        $vwc->status = $vwcStatus;

        return $vwc;
    }

    private function executeCommand(Command $command): CommandTester
    {
        $app = new Application();
        $app->addCommand($command);

        $tester = new CommandTester($app->find('api:vwc:changedToIssueDebitNote'));
        $tester->execute([]);

        return $tester;
    }
}

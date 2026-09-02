<?php

declare(strict_types=1);

namespace App\Command\Purchasing\VendorWarrantyClaim;

use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Entity\Purchasing\VendorWarrantyClaimThreshold;
use App\Notifier\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimNotifier;
use App\Repository\Finance\ExchangeRateRepository;
use App\Repository\Purchasing\VendorWarrantyClaimRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:vwc:changedToIssueDebitNote', description: 'VWC from VENDOR_TO_RESPOND to ISSUE_CREDIT_NOTE')]
class VendorWarrantyClaimAutomaticallyChangedToIssueDebitNote extends Command
{
    public const DELAY_FOR_REMINDER = 25;
    private const DELAY_FOR_CHANGE_STATUS = 30;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VendorWarrantyClaimRepository $vendorWarrantyClaimRepository,
        private readonly ExchangeRateRepository $exchangeRateRepository,
        private readonly VendorWarrantyClaimNotifier $vendorWarrantyClaimNotifier,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $vendorWarrantyClaims = $this->vendorWarrantyClaimRepository->getVendorToResponsesExpired();
        $entityManager = $this->entityManager;
        $issueDebitNoteStatus = $entityManager->getRepository(VendorWarrantyClaimStatus::class)->findOneBy(['name' => VendorWarrantyClaimStatus::ISSUE_DEBIT_NOTE]);
        $vendorWarrantyClaimThresholdsRepository = $entityManager->getRepository(VendorWarrantyClaimThreshold::class);

        /** @var VendorWarrantyClaim $vendorWarrantyClaim */
        foreach ($vendorWarrantyClaims as $vendorWarrantyClaim) {
            /** @var VendorWarrantyClaimThreshold $vendorWarrantyClaimThreshold */
            $vendorWarrantyClaimThreshold = $vendorWarrantyClaimThresholdsRepository->findOneBy(['location' => $vendorWarrantyClaim->location]);
            if (!$vendorWarrantyClaimThreshold) {
                continue;
            }

            $locationCurrencyName = $vendorWarrantyClaimThreshold->location->getCurrency()?->getName();
            if (!$locationCurrencyName) {
                continue;
            }

            $convertedRequestedCreditAmount = $this->exchangeRateRepository->convertAmount($vendorWarrantyClaim->requestedCreditAmount, $vendorWarrantyClaim->currency->getName());
            $convertedThreshold = $this->exchangeRateRepository->convertAmount($vendorWarrantyClaimThreshold->threshold, $locationCurrencyName);

            if ($convertedRequestedCreditAmount > $convertedThreshold) {
                $currentDate = new \DateTime();
                $dateDifference = $vendorWarrantyClaim->vendorToRespondAt->diff($currentDate);

                if (self::DELAY_FOR_REMINDER === $dateDifference->days) {
                    $this->vendorWarrantyClaimNotifier->sendVendorToResponseReminder($vendorWarrantyClaim);
                }

                if (self::DELAY_FOR_CHANGE_STATUS === $dateDifference->days) {
                    $vendorWarrantyClaim->status = $issueDebitNoteStatus;
                    $entityManager->persist($vendorWarrantyClaim);
                    $this->vendorWarrantyClaimNotifier->sendStatus($vendorWarrantyClaim, VendorWarrantyClaimStatus::VENDOR_TO_RESPOND);
                }
            }
        }

        $entityManager->flush();

        return Command::SUCCESS;
    }
}

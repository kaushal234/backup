<?php

declare(strict_types=1);

namespace App\Command\Evendor;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Procurement\Orders\Statistic\Contact;
use App\ION\Resources\Procurement\Orders\Statistic\PurchaseOrderStatistic;
use App\Notifier\Purchasing\VendorUserNotifier;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:vendor:weekly_notification')]
class WeeklyReminderVendorCommand extends Command
{
    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly CachedIONCollectionDataProvider $collectionDataProvider,
        private readonly EntityManagerInterface $entityManager,
        private readonly VendorUserNotifier $notifier,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Fetching Data');

        $terminated = false;
        $purchaseOrders = [];
        $lastBusinessPartner = null;

        $operation = $this->resourceMetadataCollectionFactory->create(PurchaseOrderStatistic::class)->getOperation(forceCollection: true);
        while (false === $terminated) {
            if (null === $lastBusinessPartner) {
                $purchaseOrders = [...$purchaseOrders, ...$this->collectionDataProvider->provide($operation)];
            } else {
                $purchaseOrders = [...$purchaseOrders, ...$this->collectionDataProvider->provide(
                    $operation,
                    [],
                    [
                        DataAreaFilter::CONTEXT_DATA_AREA_KEY => [
                            'pagingBP' => $lastBusinessPartner,
                        ],
                    ]
                )];
            }

            $lastOrder = end($purchaseOrders);

            if ($lastBusinessPartner === $lastOrder->buyFromSupplierCode) {
                $message = \sprintf(
                    'Skipping supplier %s: too many purchase order lines to paginate properly',
                    $lastBusinessPartner
                );
                $output->writeln($message);
                $this->logger->warning(\sprintf('Vendor Weekly reminder - %s', $message));

                break;
            }

            $terminated = end($purchaseOrders)->terminated;
            $lastBusinessPartner = end($purchaseOrders)->buyFromSupplierCode;
        }

        $progressBar = new ProgressBar($output, \count($purchaseOrders));
        /** @var PurchaseOrderStatistic $order */
        foreach ($purchaseOrders as $order) {
            $progressBar->advance();
            if ([] === array_filter($order->getContacts(), static fn (Contact $recipient) => '' !== $recipient->emailAddress)) {
                continue;
            }

            $vwcQueryBuilder = $this->entityManager->createQueryBuilder();
            $vwcQueryBuilder
                ->addSelect('COUNT(v) as vwc')
                ->from(VendorWarrantyClaim::class, 'v')
                ->leftJoin(VendorWarrantyClaimStatus::class, 'vwc_status', Join::WITH, 'vwc_status = v.status')
                ->where('v.supplierNumber = :businessPartnerCode')
                ->andWhere('vwc_status.name = :status')
                ->setParameters(new ArrayCollection([
                    new Parameter('businessPartnerCode', $order->buyFromSupplierCode),
                    new Parameter('status', VendorWarrantyClaimStatus::VENDOR_TO_RESPOND),
                ]));

            $emailContext['vwc'] = $vwcQueryBuilder->getQuery()->getScalarResult()[0]['vwc'];

            $scarQueryBuilder = $this->entityManager->createQueryBuilder();
            $scarQueryBuilder
                ->addSelect('COUNT(s) as scar')
                ->from(SupplierCorrectiveActionRequest::class, 's')
                ->where('s.supplierNumber  = :businessPartnerCode')
                ->andWhere('s.status IN (:statuses)')
                ->setParameters(new ArrayCollection([
                    new Parameter('businessPartnerCode', $order->buyFromSupplierCode),
                    new Parameter('statuses', [SupplierCorrectiveActionRequest::VENDOR_TO_FILL_FORM, SupplierCorrectiveActionRequest::COMMERCIAL_AGREEMENT]),
                ]));

            $emailContext['scar'] = $scarQueryBuilder->getQuery()->getScalarResult()[0]['scar'];

            $emailContext['lateCount'] = 0;
            $emailContext['unconfirmedCount'] = 0;
            foreach ($order->getLines() as $line) {
                if ($line->late) {
                    ++$emailContext['lateCount'];
                }
                if ($line->unconfirmed) {
                    ++$emailContext['unconfirmedCount'];
                }
            }

            if (0 === $emailContext['unconfirmedCount']
                && 0 === $emailContext['lateCount']
                && 0 === $emailContext['scar']
                && 0 === $emailContext['vwc']) {
                continue;
            }

            $this->notifier->sendWeeklyReminder($order, $emailContext);
        }

        return Command::SUCCESS;
    }
}

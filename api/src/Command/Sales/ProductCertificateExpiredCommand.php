<?php

declare(strict_types=1);

namespace App\Command\Sales;

use App\Notifier\Sales\ProductCertificateNotifier;
use App\Repository\Sales\ProductCertificateRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:sales:product_certificates_expired')]
class ProductCertificateExpiredCommand extends Command
{
    private readonly ProductCertificateRepository $productCertificateRepository;
    private readonly ProductCertificateNotifier $notifier;

    public function __construct(ProductCertificateRepository $productCertificateRepository, ProductCertificateNotifier $notifier)
    {
        parent::__construct();
        $this->setDescription('Check if CAAC Product Certificates are expired');

        $this->productCertificateRepository = $productCertificateRepository;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $expiredCertificates = $this->productCertificateRepository->findExpired();

        if ([] !== $expiredCertificates) {
            $this->notifier->sendExpiredCertificates($expiredCertificates);
        }

        return 0;
    }
}

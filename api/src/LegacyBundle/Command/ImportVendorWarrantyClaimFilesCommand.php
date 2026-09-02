<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimFile;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:purchasing:vwc_files')]
class ImportVendorWarrantyClaimFilesCommand extends Command
{
    private readonly FileHelper $fileHelper;

    public function __construct(FileHelper $fileHelper)
    {
        parent::__construct();
        $this->setDescription('Import vendor warranty claim files from legacy');
        $this->fileHelper = $fileHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fileHelper->importFromModFilesTable('VWC', VendorWarrantyClaim::class, VendorWarrantyClaimFile::class, $output, 'id');

        return 0;
    }
}

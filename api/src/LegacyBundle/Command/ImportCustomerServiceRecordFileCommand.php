<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecordFile;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:service:csr_files')]
class ImportCustomerServiceRecordFileCommand extends Command
{
    private readonly FileHelper $fileHelper;

    public function __construct(FileHelper $fileHelper)
    {
        parent::__construct();
        $this->setDescription('Import CSR files from legacy');
        $this->fileHelper = $fileHelper;
    }

    protected function configure(): void
    {
        $this
            ->addOption('start_legacy_id', 'legacy', InputOption::VALUE_OPTIONAL, 'Legacy ID of first CSR to import', 0)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fileHelper->importFromModFilesTable('CSR', AbstractCustomerServiceRecord::class, CustomerServiceRecordFile::class, $output, 'legacyId', (int) $input->getOption('start_legacy_id'));

        return 0;
    }
}

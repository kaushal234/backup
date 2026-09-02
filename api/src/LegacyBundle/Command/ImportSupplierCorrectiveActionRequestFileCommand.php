<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Entity\Quality\SupplierCorrectiveActionRequestFile;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:quality:scar_files')]
class ImportSupplierCorrectiveActionRequestFileCommand extends Command
{
    private readonly FileHelper $fileHelper;

    public function __construct(FileHelper $fileHelper)
    {
        parent::__construct();
        $this->setDescription('Import SCAR files from legacy');
        $this->fileHelper = $fileHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fileHelper->importFromModFilesTable('SCAR', SupplierCorrectiveActionRequest::class, SupplierCorrectiveActionRequestFile::class, $output, 'id');

        return Command::SUCCESS;
    }
}

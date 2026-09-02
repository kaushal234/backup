<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordFile;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:esr:file')]
class ImportEquipmentShippingRecordFilesCommand extends Command
{
    private readonly FileHelper $fileHelper;

    public function __construct(FileHelper $fileHelper)
    {
        parent::__construct();
        $this->setDescription('Import equipment shipping record files from legacy');
        $this->fileHelper = $fileHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fileHelper->importFromModFilesTable('ESR', EquipmentShippingRecord::class, EquipmentShippingRecordFile::class, $output);

        return 0;
    }
}

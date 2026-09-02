<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\SalesForecast;
use App\Entity\Sales\SalesForecastFile;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:sales_forecast_files')]
class ImportSalesForecastFileCommand extends Command
{
    private readonly FileHelper $fileHelper;

    public function __construct(FileHelper $fileHelper)
    {
        parent::__construct();
        $this->setDescription('Import sales forecast files from legacy');
        $this->fileHelper = $fileHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fileHelper->importFromModFilesTable('SFR', SalesForecast::class, SalesForecastFile::class, $output);

        return 0;
    }
}

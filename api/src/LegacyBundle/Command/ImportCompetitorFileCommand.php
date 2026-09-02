<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\Competitor;
use App\Entity\Sales\CompetitorFile;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:competitors_files')]
class ImportCompetitorFileCommand extends Command
{
    private readonly FileHelper $fileHelper;

    public function __construct(FileHelper $fileHelper)
    {
        parent::__construct();
        $this->setDescription('Import competitors files from legacy');
        $this->fileHelper = $fileHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fileHelper->importFromModFilesTable('COR', Competitor::class, CompetitorFile::class, $output);

        return 0;
    }
}

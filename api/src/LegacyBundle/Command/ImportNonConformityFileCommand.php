<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Quality\NonConformity;
use App\Entity\Quality\NonConformityFile;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:quality:ncr_files')]
class ImportNonConformityFileCommand extends Command
{
    private readonly FileHelper $fileHelper;
    private readonly Connection $legacyConnection;

    public function __construct(FileHelper $fileHelper, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Import NCR files from legacy');
        $this->fileHelper = $fileHelper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT id, parent_id, date, description, filename
            FROM ncr_files
            WHERE filename <> ''
            SQL;
        $result = $this->legacyConnection->executeQuery($sql);

        $this->fileHelper->import(
            $result,
            $output,
            NonConformity::class,
            NonConformityFile::class,
            'filename',
            'parent_id',
            'ncr_files',
            static function ($data) {
                $metadata = [];
                if ($data['date']) {
                    $metadata['created_at'] = new \DateTime($data['date']);
                }

                if ($data['description']) {
                    $metadata['description'] = $data['description'];
                }

                return $metadata;
            },
            'id'
        );

        return Command::SUCCESS;
    }
}

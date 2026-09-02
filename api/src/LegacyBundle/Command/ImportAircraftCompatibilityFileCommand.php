<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\AircraftCompatibility\AircraftCompatibility;
use App\Entity\Sales\AircraftCompatibility\AircraftCompatibilityFile;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:nto_files', description: 'Import Aircraft Compatibility files')]
class ImportAircraftCompatibilityFileCommand extends Command
{
    public function __construct(
        private readonly FileHelper $fileHelper,
        private readonly Connection $legacyConnection,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT id, parent_id, date, description, filename, category
            FROM nto
            WHERE filename <> ''
            SQL;
        $result = $this->legacyConnection->executeQuery($sql);

        $this->fileHelper->import(
            $result,
            $output,
            AircraftCompatibility::class,
            AircraftCompatibilityFile::class,
            'filename',
            'id',
            'nto',
            static function ($data) {
                $metadata = [];
                if ($data['date']) {
                    $metadata['created_at'] = new \DateTime($data['date']);
                }

                if ($data['description']) {
                    $metadata['description'] = $data['description'];
                }

                if ($data['category']) {
                    $metadata['type'] = $data['category'];
                }

                return $metadata;
            }
        );

        return Command::SUCCESS;
    }
}

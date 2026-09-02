<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Entity\Quality\SupplierCorrectiveActionRequestMainFile;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:quality:scar_main_files')]
class ImportSupplierCorrectiveActionRequestMainFileCommand extends Command
{
    private readonly FileHelper $fileHelper;
    private readonly Connection $legacyConnection;

    public function __construct(FileHelper $fileHelper, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Import SCAR files from legacy');
        $this->fileHelper = $fileHelper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT id, file_name, dt
            FROM scar
            WHERE file_name <> ''
            SQL;
        $result = $this->legacyConnection->executeQuery($sql);

        $this->fileHelper->import(
            $result,
            $output,
            SupplierCorrectiveActionRequest::class,
            SupplierCorrectiveActionRequestMainFile::class,
            'file_name',
            'id',
            'scar',
            static function ($data) {
                $metadata = [];
                if ($data['dt']) {
                    $metadata['created_at'] = new \DateTime($data['dt']);
                }

                return $metadata;
            },
            'id'
        );

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\ForecastClosure;
use App\Entity\Sales\ForecastClosureFile;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:forecast_closure_files')]
class ImportForecastClosureFileCommand extends Command
{
    private readonly FileHelper $fileHelper;

    private readonly Connection $legacyConnection;

    public function __construct(
        FileHelper $fileHelper,
        Connection $legacyConnection
    ) {
        parent::__construct();
        $this->setDescription('Import forecast closure files from legacy');
        $this->fileHelper = $fileHelper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT fcr.id, fcr.parent_id, fcr.filename, created
            FROM fcr
            LEFT JOIN sfr ON fcr.parent_id = sfr.id
            WHERE sfr.id IS NOT NULL AND fcr.filename <> ''
            SQL;

        $this->fileHelper->import(
            $this->legacyConnection->executeQuery($sql),
            $output,
            ForecastClosure::class,
            ForecastClosureFile::class,
            'filename',
            'id',
            'fcr',
            static function ($data) {
                $metadata = [];
                if ($data['created']) {
                    $metadata['created_at'] = new \DateTime($data['created']);
                }

                return $metadata;
            });

        return 0;
    }
}

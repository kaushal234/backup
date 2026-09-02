<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\AircraftCompatibility\Aircraft;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:aircraft')]
class ImportAircraftCommand extends Command
{
    public function __construct(
        private readonly ImportHelper $helper,
        private readonly Connection $legacyConnection,
    ) {
        parent::__construct();
        $this->setDescription('Imports Aircrafts from legacy');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import aircrafts
        $sql = <<<'SQL'
            SELECT MIN(id) AS id, list_item FROM lists WHERE list_name='AIRCRAFT' GROUP BY list_item ORDER BY list_item ASC
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->progressiveImport(
            $output, $stmt, Aircraft::class, 'id', 'id',
            static function (Aircraft $aircraft, array $data) {
                $aircraft->name = $data['list_item'];
            }, true
        );

        return Command::SUCCESS;
    }
}

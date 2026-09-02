<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\Position;
use App\Entity\Directory\PositionLevel;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:directory:positions')]
class ImportDirectoryPositionsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    /**
     * ImportDirectoryPositionsCommand constructor.
     */
    public function __construct(ImportHelper $helper, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Imports positions from legacy tld_functions table');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import levels
        $sql = <<<'SQL'
            SELECT DISTINCT level as level_name
            FROM tld_functions
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $levels = $this->helper->progressiveImport(
            $output, $stmt, PositionLevel::class, 'label', 'level_name',
            static function (PositionLevel $level, array $data) {
                $level
                    ->setLabel($data['level_name']);
            }
        );

        // Import positions
        $sql = <<<'SQL'
            SELECT id, code, dsc, level
            FROM tld_functions
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Position::class, 'legacyId', 'id',
            static function (Position $position, array $data) use ($levels) {
                $position
                    ->setCode($data['code'])
                    ->setDescription($data['dsc'])
                    ->setLevel($levels[$data['level']])
                ;
            }
        );

        return 0;
    }
}

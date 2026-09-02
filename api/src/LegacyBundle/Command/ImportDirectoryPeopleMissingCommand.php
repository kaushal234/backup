<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:directory:people-missing')]
class ImportDirectoryPeopleMissingCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    /**
     * ImportDirectoryPeopleMissingCommand constructor.
     */
    public function __construct(ImportHelper $helper, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Imports people missing fields from legacy people table');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT id, baan_id, baan_employee_id, windows_id
            FROM people
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, People::class, 'legacyId', 'id',
            static function (People $people, array $data) {
                if (null === $people->getErpLogin()) {
                    $people->setErpLogin($data['baan_id']);
                }
                if (null === $people->getErpIdentifier() && (int) $data['baan_employee_id'] > 0) {
                    $people->setErpIdentifier((string) $data['baan_employee_id']);
                }
                if (null === $people->getWindowsLogin()) {
                    $people->setWindowsLogin($data['windows_id']);
                }
            }, true
        );

        return 0;
    }
}

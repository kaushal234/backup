<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\Department;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:directory:departments')]
class ImportDirectoryDepartmentsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    /**
     * ImportDirectoryDepartmentsCommand constructor.
     */
    public function __construct(ImportHelper $helper, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Imports departments from legacy tld_departments table');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import business units
        $sql = <<<'SQL'
            SELECT id, dpt, sso, erp
            FROM tld_departments
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Department::class, 'legacyId', 'id',
            static function (Department $department, array $data) {
                $department
                    ->setName($data['dpt'])
                    ->setSso('Y' === $data['sso'])
                    ->setFactory('Y' === $data['erp'])
                ;
            }
        );

        return 0;
    }
}

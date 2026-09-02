<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\JuridicalLocation;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\AddressHelper;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:directory:juridical-locations')]
class ImportDirectoryJuridicalLocationsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly AddressHelper $addressHelper;

    /**
     * ImportDirectoryJuridicalLocationsCommand constructor.
     */
    public function __construct(ImportHelper $helper, Connection $legacyConnection, AddressHelper $addressHelper)
    {
        parent::__construct();
        $this->setDescription('Imports juridical locations from legacy tld_juridical_locations table');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->addressHelper = $addressHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import juridical locations
        $sql = <<<'SQL'
            SELECT id, name, address
            FROM tld_juridical_locations
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, JuridicalLocation::class, 'legacyId', 'id',
            function (JuridicalLocation $juridicalLocation, array $data) {
                $juridicalLocation
                    ->setName($data['name'])
                    ->setAddress($this->addressHelper->parseAddress($data['address']))
                ;
            }
        );

        return 0;
    }
}

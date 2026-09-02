<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Country;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:countries')]
class ImportCountriesCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    /**
     * ImportCountriesCommand constructor.
     */
    public function __construct(ImportHelper $helper, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Import countries from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import countries
        $sql = <<<'SQL'
            SELECT *
            FROM countries;
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Country::class, 'legacyId', 'id',
            static function (Country $country, array $data) {
                $country
                    ->setLegacyId((int) $data['id'])
                    ->setName($data['name'])
                    ->setAlternateNames($data['alt_name'])
                    ->setIsoCode2($data['iso_code_2'])
                    ->setIsoCode3($data['iso_code_3'])
                    ->setNbCode($data['nb_code'])
                    ->setFipsCode($data['fips_code'])
                    ->setFipsName($data['fips_name'])
                    ->setRegion($data['region'])
                    ->setSubRegion($data['sub_region'])
                    ->setLatitude($data['gps_lat'])
                    ->setLongitude($data['gps_long'])
                ;
            }, true
        );

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Common\Airport;
use App\Entity\Common\BusStation;
use App\Entity\Common\FerryPort;
use App\Entity\Common\Heliport;
use App\Entity\Common\MetropolitanArea;
use App\Entity\Common\OffLinePoint;
use App\Entity\Common\RailwayStation;
use App\Entity\Country;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:iata-codes')]
class ImportIATACodeCommand extends Command
{
    private readonly ImportHelper $importHelper;

    private readonly EntityCacheHelperFactory $cacheHelperFactory;
    private readonly Connection $legacyConnection;
    private readonly SanitationHelper $sanitationHelper;

    public function __construct(
        ImportHelper $importHelper,
        SanitationHelper $sanitationHelper,
        EntityCacheHelperFactory $cacheHelperFactory,
        Connection $legacyConnection
    ) {
        parent::__construct();
        $this->setDescription('Import iata codes from legacy');
        $this->importHelper = $importHelper;
        $this->cacheHelperFactory = $cacheHelperFactory;
        $this->legacyConnection = $legacyConnection;
        $this->sanitationHelper = $sanitationHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $countryCache = $this->cacheHelperFactory->createEntityCache(Country::class, 'isoCode2');
        $sanitationHelper = $this->sanitationHelper;

        $sql = <<<'SQL'
            SELECT id, city_code_3, city_name, state, ctry_code_2, airport_code, airport_name, airport_numeric, source, type
            FROM airport_codes
            WHERE type = 'Airport'
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->importHelper->progressiveImport(
            $output, $stmt, Airport::class, 'legacyId', 'id',
            static function (Airport $airport, array $data) use ($countryCache, $sanitationHelper) {
                $airport
                    ->setCityCode3($sanitationHelper->trimAndNullify($data['city_code_3']))
                    ->setCityName($data['city_name'])
                    ->setState($sanitationHelper->trimAndNullify($data['state']))
                    ->setCountry($countryCache->fetch($data['ctry_code_2']))
                    ->setCode($data['airport_code'])
                    ->setName($sanitationHelper->trimAndNullify($data['airport_name']))
                    ->setSource($data['source'])
                    ->setType($data['type'])
                ;
            }, true
        );

        $sql = <<<'SQL'
            SELECT id, city_code_3, city_name, state, ctry_code_2, airport_code, airport_name, airport_numeric, source, type
            FROM airport_codes
            WHERE type = 'Railway Station'
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->importHelper->progressiveImport(
            $output, $stmt, RailwayStation::class, 'legacyId', 'id',
            static function (RailwayStation $airport, array $data) use ($countryCache, $sanitationHelper) {
                $airport
                    ->setCityCode3($sanitationHelper->trimAndNullify($data['city_code_3']))
                    ->setCityName($data['city_name'])
                    ->setState($sanitationHelper->trimAndNullify($data['state']))
                    ->setCountry($countryCache->fetch($data['ctry_code_2']))
                    ->setCode($data['airport_code'])
                    ->setName($sanitationHelper->trimAndNullify($data['airport_name']))
                    ->setSource($data['source'])
                    ->setType($data['type'])
                ;
            }, true
        );

        $sql = <<<'SQL'
            SELECT id, city_code_3, city_name, state, ctry_code_2, airport_code, airport_name, airport_numeric, source, type
            FROM airport_codes
            WHERE type = 'Bus Station'
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->importHelper->progressiveImport(
            $output, $stmt, BusStation::class, 'legacyId', 'id',
            static function (BusStation $airport, array $data) use ($countryCache, $sanitationHelper) {
                $airport
                    ->setCityCode3($sanitationHelper->trimAndNullify($data['city_code_3']))
                    ->setCityName($data['city_name'])
                    ->setState($sanitationHelper->trimAndNullify($data['state']))
                    ->setCountry($countryCache->fetch($data['ctry_code_2']))
                    ->setCode($data['airport_code'])
                    ->setName($sanitationHelper->trimAndNullify($data['airport_name']))
                    ->setSource($data['source'])
                    ->setType($data['type'])
                ;
            }, true
        );

        $sql = <<<'SQL'
            SELECT id, city_code_3, city_name, state, ctry_code_2, airport_code, airport_name, airport_numeric, source, type
            FROM airport_codes
            WHERE type = 'Off-Line Point'
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->importHelper->progressiveImport(
            $output, $stmt, OffLinePoint::class, 'legacyId', 'id',
            static function (OffLinePoint $airport, array $data) use ($countryCache, $sanitationHelper) {
                $airport
                    ->setCityCode3($sanitationHelper->trimAndNullify($data['city_code_3']))
                    ->setCityName($data['city_name'])
                    ->setState($sanitationHelper->trimAndNullify($data['state']))
                    ->setCountry($countryCache->fetch($data['ctry_code_2']))
                    ->setCode($data['airport_code'])
                    ->setName($sanitationHelper->trimAndNullify($data['airport_name']))
                    ->setSource($data['source'])
                    ->setType($data['type'])
                ;
            }, true
        );

        $sql = <<<'SQL'
            SELECT id, city_code_3, city_name, state, ctry_code_2, airport_code, airport_name, airport_numeric, source, type
            FROM airport_codes
            WHERE type = 'Metropolitan Area'
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->importHelper->progressiveImport(
            $output, $stmt, MetropolitanArea::class, 'legacyId', 'id',
            static function (MetropolitanArea $airport, array $data) use ($countryCache, $sanitationHelper) {
                $airport
                    ->setCityCode3($sanitationHelper->trimAndNullify($data['city_code_3']))
                    ->setCityName($data['city_name'])
                    ->setState($sanitationHelper->trimAndNullify($data['state']))
                    ->setCountry($countryCache->fetch($data['ctry_code_2']))
                    ->setCode($data['airport_code'])
                    ->setName($sanitationHelper->trimAndNullify($data['airport_name']))
                    ->setSource($data['source'])
                    ->setType($data['type'])
                ;
            }, true
        );

        $sql = <<<'SQL'
            SELECT id, city_code_3, city_name, state, ctry_code_2, airport_code, airport_name, airport_numeric, source, type
            FROM airport_codes
            WHERE type = 'Ferry Port'
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->importHelper->progressiveImport(
            $output, $stmt, FerryPort::class, 'legacyId', 'id',
            static function (FerryPort $airport, array $data) use ($countryCache, $sanitationHelper) {
                $airport
                    ->setCityCode3($sanitationHelper->trimAndNullify($data['city_code_3']))
                    ->setCityName($data['city_name'])
                    ->setState($sanitationHelper->trimAndNullify($data['state']))
                    ->setCountry($countryCache->fetch($data['ctry_code_2']))
                    ->setCode($data['airport_code'])
                    ->setName($sanitationHelper->trimAndNullify($data['airport_name']))
                    ->setSource($data['source'])
                    ->setType($data['type'])
                ;
            }, true
        );

        $sql = <<<'SQL'
            SELECT id, city_code_3, city_name, state, ctry_code_2, airport_code, airport_name, airport_numeric, source, type
            FROM airport_codes
            WHERE type = 'Heliport'
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->importHelper->progressiveImport(
            $output, $stmt, Heliport::class, 'legacyId', 'id',
            static function (Heliport $airport, array $data) use ($countryCache, $sanitationHelper) {
                $airport
                    ->setCityCode3($sanitationHelper->trimAndNullify($data['city_code_3']))
                    ->setCityName($data['city_name'])
                    ->setState($sanitationHelper->trimAndNullify($data['state']))
                    ->setCountry($countryCache->fetch($data['ctry_code_2']))
                    ->setCode($data['airport_code'])
                    ->setName($sanitationHelper->trimAndNullify($data['airport_name']))
                    ->setSource($data['source'])
                    ->setType($data['type'])
                ;
            }, true
        );

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\AddressWithCountry;
use App\Entity\Directory\JuridicalLocation;
use App\Entity\Directory\Location;
use App\Entity\Directory\LocationCapability;
use App\Entity\Directory\LocationContact;
use App\Entity\Directory\LocationState;
use App\Entity\Directory\People;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\PhoneHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;

#[AsCommand(name: 'legacy:import:directory:locations')]
class ImportDirectoryLocationsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    private readonly PhoneHelper $phoneHelper;

    /**
     * ImportDirectoryLocationsCommand constructor.
     */
    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, PhoneHelper $phoneHelper)
    {
        parent::__construct();
        $this->setDescription('Imports locations from legacy locations table');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->phoneHelper = $phoneHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $juridicalLocationCache = $this->cacheFactory->createEntityCache(JuridicalLocation::class, 'legacyId');
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');

        // Import locations
        // The WHERE clause is a hack to filter location created by the buggy double write that can't be deleted
        $sql = <<<'SQL'
            SELECT *
            FROM locations
            WHERE hq != ''
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Location::class, 'legacyId', 'id',
            function (Location $location, array $data) use ($juridicalLocationCache, $peopleCache) {
                $location
                    ->setLegacyId((int) $data['id'])
                    ->setName($data['location'])
                    ->setCompany($data['company_name'])
                    ->setErp((int) $data['erp'])
                    ->setCurrency($this->getCurrencyCode($data['dcur']))
                    ->setCapability((new LocationCapability())
                        ->setSso('SSO' === $data['role'])
                        ->setFactory('Y' === $data['factory'])
                        ->setWarehouse('Y' === $data['warehouse'])
                        ->setSparePartsHub('Y' === $data['sph'])
                        ->setServiceHub('Y' === $data['sh'])
                        ->setHeadQuarter('Y' === $data['hq'])
                    )
                    ->setContact((new LocationContact())
                        ->setTelephone($this->phoneHelper->parsePhone($data['tel']))
                        ->setFax($this->phoneHelper->parsePhone($data['fax']))
                        ->setServiceHubEmail($data['sh_email'] ?: null)
                        ->setServiceHubTelephone($this->phoneHelper->parsePhone($data['sh_tel']))
                        ->setSparePartsEmail($data['sph_email'] ?: null)
                        ->setSparePartsTelephone($this->phoneHelper->parsePhone($data['parts_tel']))
                        ->setSparePartsFax($this->phoneHelper->parsePhone($data['parts_fax']))
                    )
                    ->setState((new LocationState())
                        ->setPublic('1' === $data['public'])
                        ->setHidden('1' === $data['hidden'])
                        ->setDisabled('1' === $data['disable'])
                    )
                    ->setAddress((new AddressWithCountry())
                        ->setStreet1($data['street1'] ?: null)
                        ->setStreet2($data['street2'] ?: null)
                        ->setPostalCode($data['postal_code'] ?: null)
                        ->setTown($data['town'] ?: null)
                        ->setCity($data['city'] ?: null)
                        ->setState($data['state'] ?: null)
                        ->setCountry($this->getCountryCode($data['country']))
                    )
                    ->setInternalNetworkAddress($data['fw_inside_network'] ?: null)
                    ->setDomain($data['email_domain'] ?: null)
                    ->setTimeZone(isset($data['timezone']) ? ($data['timezone'] ?: null) : null)
                    ->setBusinessUnit(null);

                if ($data['juridical_location_id']) {
                    $location->setJuridicalLocation($juridicalLocationCache->fetch($data['juridical_location_id']));
                } else {
                    $location->setJuridicalLocation(null);
                }

                if ($data['repid']) {
                    $location->setRepresentative($peopleCache->fetch($data['repid']));
                } else {
                    $location->setRepresentative(null);
                }
            });

        return 0;
    }

    private function getCountryCode($needle)
    {
        $needle = mb_trim((string) $needle);

        if ('' === $needle) {
            return;
        }

        $countries = Countries::getNames('en_US');
        if (isset($countries[$needle])) {
            return $needle;
        }
        if (false !== $code = array_search(ucwords(mb_strtolower($needle)), $countries, true)) {
            return $code;
        }

        $magic = [
            'hong kong sar' => 'Hong Kong SAR China',
            'PEOPLE\\\'S REPUBLIC OF CHINA' => 'China',
            'P.R.C.' => 'China',
            'UNITED STATES OF AMERICA' => 'United States',
            'USA' => 'United States',
        ];
        foreach ($magic as $search => $replace) {
            $needle = preg_replace('/^'.preg_quote($search, '/').'$/i', $replace, $needle);
        }

        if (false !== $code = array_search($needle, $countries, true)) {
            return $code;
        }
        throw new \RuntimeException('Unable to find the country named: '.$needle);
    }

    private function getCurrencyCode($needle)
    {
        $needle = mb_trim((string) $needle);
        if ('' === $needle) {
            return;
        }

        $currencies = Currencies::getNames('en_US');
        if (isset($currencies[$needle])) {
            return $needle;
        }
        if (false !== $code = array_search(ucwords(mb_strtolower($needle)), $currencies, true)) {
            return $code;
        }

        throw new \RuntimeException('Unable to find the country named: '.$needle);
    }
}

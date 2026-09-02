<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Common\Airport;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EmissionRating;
use App\Entity\Module\Module;
use App\Entity\Sales\Customer;
use App\Entity\Sales\MasterSalesForecast;
use App\Entity\Sales\Product;
use App\Entity\Sales\SalesForecast;
use App\Repository\Module\ModuleRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:sfr')]
class ImportSalesForecastsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    private readonly ManagerRegistry $registry;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, ManagerRegistry $registry)
    {
        parent::__construct();
        $this->setDescription('Imports SFR from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'legacyId');
        $masterSalesForecastCache = $this->cacheFactory->createEntityCache(MasterSalesForecast::class, 'legacyId');
        $customerCache = $this->cacheFactory->createEntityCache(Customer::class, 'legacyId');
        $customerNameCache = $this->cacheFactory->createEntityCache(Customer::class, 'name');
        $airportCache = $this->cacheFactory->createEntityCache(Airport::class, 'code');
        $productCache = $this->cacheFactory->createEntityCache(Product::class, 'name');
        $emissionRatingCache = $this->cacheFactory->createEntityCache(EmissionRating::class, 'name');
        $countryCache = $this->cacheFactory->createEntityCache(Country::class, 'name');

        // Import groups
        $sql = <<<'SQL'
            SELECT id, sfr_master_id, dt, dt_closed, status, sso_id, erp_id, asm_id, init_id, equote_id, buyer_customer_id, cust_nama, cust_ctry, user_customer_id, third_party_id, apc, model, qty, year_id, month_id, cust_pur_pc, tld_succ_pc, eng_tier,
            (SELECT date FROM mod_logs logs WHERE logs.module ="SFR" AND logs.parent_id = sfr.id ORDER BY id DESC LIMIT 1) AS last_updated_at
            FROM sfr
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        /** @var ModuleRepository $moduleRepository */
        $moduleRepository = $this->registry->getManager()->getRepository(Module::class);

        /** @var Module $sfrModule */
        $sfrModule = $moduleRepository->findByName('SFR');
        $moo = $sfrModule->getOperationalOwner();

        $this->helper->progressiveImport(
            $output, $stmt, SalesForecast::class, 'legacyId', 'id',
            function (SalesForecast $salesForecast, array $data) use ($peopleCache, $locationCache, $masterSalesForecastCache, $customerCache, $customerNameCache, $airportCache, $productCache, $emissionRatingCache, $countryCache, $moo) {
                /** @var Customer|null $customer */
                $customer = null !== $data['buyer_customer_id'] ? $customerCache->fetch($data['buyer_customer_id']) : $customerNameCache->fetch($data['cust_nama']);

                if (null === $data['sfr_master_id'] || null === $masterSalesForecast = $masterSalesForecastCache->fetch($data['sfr_master_id'])) {
                    $masterSalesForecast = new MasterSalesForecast();
                    $this->legacyConnection->executeStatement("INSERT INTO sfr_master (name) VALUES ('');");
                    $masterSalesForecast->setLegacyId((int) $this->legacyConnection->lastInsertId());
                    $this->registry->getManager()->persist($masterSalesForecast);
                }

                $salesForecast
                    ->setLegacyId((int) $data['id'])
                    ->setMasterSalesForecast($masterSalesForecast)
                    ->setCreatedAt(new \DateTime($data['dt']))
                    ->setClosedAt(null === $data['dt_closed'] ? null : new \DateTime($data['dt_closed']))
                    ->setStatus($data['status'])
                    ->setSso($locationCache->fetch($data['sso_id']))
                    ->setFactory($locationCache->fetch($data['erp_id']))
                    ->setAsm($peopleCache->fetch($data['asm_id']) ?? $moo)
                    ->setPoster($peopleCache->fetch($data['init_id']) ?? $moo)
                    ->setEquoteId('' !== $data['equote_id'] ? $data['equote_id'] : null)
                    ->setBuyer($customer)
                    ->setEndUser($customerCache->fetch($data['user_customer_id']))
                    ->setThirdParty($customerCache->fetch($data['third_party_id']))
                    ->setAirport($airportCache->fetch($data['apc']))
                    ->setProduct($productCache->fetch($data['model']))
                    ->setQuantity((int) $data['qty'])
                    ->setEstimatedSaleDate(new \DateTime(\sprintf('%s-%s-01', $data['year_id'], $data['month_id'])))
                    ->setCustomerSuccessPercentage((int) $data['cust_pur_pc'])
                    ->setSuccessPercentage((int) $data['tld_succ_pc'])
                    ->setTier($emissionRatingCache->fetch($data['eng_tier']))
                    ->setCountry($countryCache->fetch($data['cust_ctry']))
                    ->setUpdatedAt(new \DateTime($data['last_updated_at']))
                    ->setLastComment(\sprintf('SFR for customer %s', null !== $customer ? $customer->getName() : $data['cust_nama']))
                ;
            }, true
        );

        return 0;
    }
}

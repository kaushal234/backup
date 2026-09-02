<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerRelationshipTeam;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:crt')]
class ImportSalesCustomerRelationshipTeamCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    /**
     * ImportSalesCustomerRelationshipTeamCommand constructor.
     */
    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory)
    {
        parent::__construct();
        $this->setDescription('Import TLD legacy cutomer relationship team');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'legacyId');
        $customerCache = $this->cacheFactory->createEntityCache(Customer::class, 'legacyId');

        // Import customers
        $sql = <<<'SQL'
            SELECT id, parent_id, customer_id, sales_rep_id, parts_rep_id, services_rep_id, parts_location_id, services_location_id, erp_location_id, cuno
            FROM customers_crt;
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, CustomerRelationshipTeam::class, 'legacyId', 'id',
            static function (CustomerRelationshipTeam $customerRelationshipteam, array $data) use ($peopleCache, $locationCache, $customerCache) {
                $cuno = empty(mb_trim((string) $data['cuno'])) ? null : mb_trim((string) $data['cuno']);
                $customerRelationshipteam
                    ->setLegacyId((int) $data['id'])
                    ->setCustomer($customerCache->fetch($data['customer_id']))
                    ->setSalesRepresentative($peopleCache->fetch($data['sales_rep_id']))
                    ->setPartsRepresentative($peopleCache->fetch($data['parts_rep_id']))
                    ->setServiceRepresentative($peopleCache->fetch($data['services_rep_id']))
                    ->setPartsLocation($locationCache->fetch($data['parts_location_id']))
                    ->setServiceLocation($locationCache->fetch($data['services_location_id']))
                    ->setErpLocation($locationCache->fetch($data['erp_location_id']))
                    ->setCuno($cuno)
                ;
            }, true
        );

        return 0;
    }
}

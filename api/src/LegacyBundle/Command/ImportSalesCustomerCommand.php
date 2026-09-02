<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Country;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerType;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\FileHelper;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:customers')]
class ImportSalesCustomerCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    private readonly SanitationHelper $sanitationHelper;

    private readonly FileHelper $fileHelper;

    /**
     * ImportSalesCustomerCommand constructor.
     */
    public function __construct(
        ImportHelper $helper,
        Connection $legacyConnection,
        EntityCacheHelperFactory $cacheFactory,
        SanitationHelper $sanitationHelper,
        FileHelper $fileHelper
    ) {
        parent::__construct();
        $this->setDescription('Import TLD legacy customers and customer types');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->sanitationHelper = $sanitationHelper;
        $this->fileHelper = $fileHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $typeCache = $this->cacheFactory->createEntityCache(CustomerType::class, 'name');
        $countryCache = $this->cacheFactory->createEntityCache(Country::class, 'legacyId');

        // Import customer types
        $sql = <<<'SQL'
            SELECT id, list_item
            FROM lists
            WHERE list_name = 'list.sales.customer.types'
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, CustomerType::class, 'legacyId', 'id',
            static function (CustomerType $type, array $data) {
                $type
                    ->setLegacyId((int) $data['id'])
                    ->setName($data['list_item'])
                ;
            }, true
        );

        // Import customers
        $sql = <<<'SQL'
            SELECT id, parent_id, customer_name, customer_short_name, customer_address, ctry_id, customer_tel, customer_fax, type, hidden, approved, logo_file, url, asm_id
            FROM customers
            GROUP BY REPLACE(customer_name, "\\", '');
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Customer::class, 'legacyId', 'id',
            function (Customer $customer, array $data) use ($typeCache, $peopleCache, $countryCache) {
                $phone = empty($data['customer_tel']) ? null : $data['customer_tel'];
                $fax = empty($data['customer_fax']) ? null : $data['customer_fax'];
                $logo = $this->fileHelper->parse($data['logo_file'], ['jpg', 'jpeg', 'png']);
                $logo = null !== $logo ? 'customers/'.$logo : null;
                $customer
                    ->setLegacyId((int) $data['id'])
                    ->setName(mb_trim($this->sanitationHelper->parse($data['customer_name'])))
                    ->setLegacyAddress($data['customer_address'])
                    ->setPhone($phone)
                    ->setFax($fax)
                    ->addCustomerType($typeCache->fetch($data['type']))
                    ->setHidden((bool) $data['hidden'])
                    ->setStatus((bool) $data['approved'] ? 'APPROVED' : 'NOT APPROVED')
                    ->setLogo($logo)
                    ->setCountry($countryCache->fetch($data['ctry_id']))
                    ->setAsm($peopleCache->fetch($data['asm_id']))
                    ->setUrl($this->sanitationHelper->normalizeUrl($data['url']))
                ;
            }, true
        );

        // Import customers parents
        $sql = <<<'SQL'
            SELECT id, parent_id
            FROM customers
            WHERE parent_id > 0;
            SQL;
        $customerCache = $this->cacheFactory->createEntityCache(Customer::class, 'legacyId');
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Customer::class, 'legacyId', 'id',
            static function (Customer $customer, array $data) use ($customerCache) {
                $customer
                    ->setParentCustomer($customerCache->fetch($data['parent_id']))
                ;
            }, true
        );

        return 0;
    }
}

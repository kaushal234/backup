<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\DMS;
use App\Entity\Sales\ProductFamily;
use App\Entity\Sales\ProductFamilyDMS;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:product_family_dms')]
class ImportSalesCatalogueProductFamilyDmsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory)
    {
        parent::__construct();
        $this->setDescription('Import TLD legacy catalogue product family DMS');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $familyCache = $this->cacheFactory->createEntityCache(ProductFamily::class, 'legacyId');
        $dmsCache = $this->cacheFactory->createEntityCache(DMS::class, 'legacyId');
        // Import catalog product families
        $sql = <<<'SQL'
            SELECT id, parent_id, dms_id, type
            FROM products_datasheets_dms
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->progressiveImport(
            $output, $stmt, ProductFamilyDMS::class, 'legacyId', 'id',
            static function (ProductFamilyDMS $family_dms, array $data) use ($familyCache, $dmsCache) {
                $family_dms
                    ->setLegacyId((int) $data['id'])
                    ->setDms($dmsCache->fetch($data['dms_id']))
                    ->setDmsType($data['type'])
                    ->setFamily($familyCache->fetch($data['parent_id']))
                ;
            }, true
        );

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\Location;
use App\Entity\Sales\Product;
use App\Entity\Sales\ProductFamily;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:products')]
class ImportSalesCatalogueProductsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory)
    {
        parent::__construct();
        $this->setDescription('Import TLD legacy catalogue products');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $familyCache = $this->cacheFactory->createEntityCache(ProductFamily::class, 'name');
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'erp');

        $sql = <<<'SQL'
            SELECT id, family, model, hide, erpid
            FROM models
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Product::class, 'legacyId', 'id',
            static function (Product $product, array $data) use ($locationCache, $familyCache) {
                $product
                    ->setLegacyId((int) $data['id'])
                    ->setName($data['model'])
                    ->setHidden((bool) $data['hide'])
                    ->setErpLocation($locationCache->fetch($data['erpid']))
                    ->setFamily($familyCache->fetch($data['family']))
                ;
            }, true
        );

        return 0;
    }
}

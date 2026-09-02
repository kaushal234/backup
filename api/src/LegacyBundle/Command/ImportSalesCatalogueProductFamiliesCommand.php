<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\ProductFamily;
use App\Entity\Sales\ProductType;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:product_families')]
class ImportSalesCatalogueProductFamiliesCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    private readonly SanitationHelper $sanitationHelper;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper)
    {
        parent::__construct();
        $this->setDescription('Import TLD legacy catalogue product families');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->sanitationHelper = $sanitationHelper;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productTypesCache = $this->cacheFactory->createEntityCache(ProductType::class, 'legacyId');

        $request = <<< 'SQL'
            SELECT model FROM products_datasheets;
            SQL;
        $stmt = $this->legacyConnection->executeQuery($request);
        $existingFamilies = array_column($stmt->fetchAllAssociative(), 'model');

        $sql = <<< 'SQL'
            SELECT family, parent_id, IF(SUM(hide) = COUNT(family), 1, 0) as hidden FROM models GROUP BY family;
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $families = $stmt->fetchAllAssociative();

        foreach ($families as $family) {
            if (!\in_array($family['family'], $existingFamilies, true)) {
                $request = <<< 'SQL'
                    INSERT INTO products_datasheets (model, parent_id, hidden, public) VALUES (:family, :parent_id, :hide, :public)
                    SQL;
                $hidden = (bool) $family['hidden'];
                $this->legacyConnection->executeQuery($request,
                    [
                        'family' => $family['family'],
                        'parent_id' => $family['parent_id'],
                        'hide' => (int) $hidden,
                        'public' => (int) !$hidden,
                    ]
                );

                $existingFamilies[] = $family['family'];
            }
        }

        // Import catalog product families
        $sql = <<<'SQL'
            SELECT id, en, fr, es, pt, zh, ja, de, ru, hidden, public, model, parent_id
            FROM products_datasheets
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, ProductFamily::class, 'legacyId', 'id',
            function (ProductFamily $family, array $data) use ($productTypesCache) {
                $family
                    ->setLegacyId((int) $data['id'])
                    ->setEnglishDescription(mb_trim($this->sanitationHelper->parse($data['en'], true, true, true, false)))
                    ->setFrenchDescription(mb_trim($this->sanitationHelper->parse($data['fr'], true, true, true, false)))
                    ->setSpanishDescription(mb_trim($this->sanitationHelper->parse($data['es'], true, true, true, false)))
                    ->setPortugueseDescription(mb_trim($this->sanitationHelper->parse($data['pt'], true, true, true, false)))
                    ->setChineseDescription(mb_trim($this->sanitationHelper->parse($data['zh'], true, true, true, false)))
                    ->setJapaneseDescription(mb_trim($this->sanitationHelper->parse($data['ja'], true, true, true, false)))
                    ->setGermanDescription(mb_trim($this->sanitationHelper->parse($data['de'], true, true, true, false)))
                    ->setRussianDescription(mb_trim($this->sanitationHelper->parse($data['ru'], true, true, true, false)))
                    ->setHidden((bool) $data['hidden'])
                    ->setPublicForTLD((bool) $data['public'])
                    ->setProductType($productTypesCache->fetch($data['parent_id']))
                    ->setName($data['model'])
                ;
            }, true
        );

        return 0;
    }
}

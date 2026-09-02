<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\Competitor;
use App\Entity\Sales\ProductType;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:competitors')]
class ImportCompetitorCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    private readonly SanitationHelper $sanitationHelper;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper)
    {
        parent::__construct();
        $this->setDescription('Import competitors from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->sanitationHelper = $sanitationHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT cor.id, company_name, short_desc, comments, url, GROUP_CONCAT(DISTINCT cor_prod.type SEPARATOR '|') AS product_types
            FROM cor
              LEFT JOIN cor_prod ON cor.id = cor_prod.parent_id
            GROUP BY cor.id;
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $productTypeCache = $this->cacheFactory->createEntityCache(ProductType::class, 'englishName');

        $this->helper->progressiveImport(
            $output, $stmt, Competitor::class, 'legacyId', 'id',
            function (Competitor $competitor, array $data) use ($productTypeCache) {
                $url = null;

                $competitor
                    ->setLegacyId((int) $data['id'])
                    ->setName($data['company_name'])
                    ->setShortDescription($data['short_desc'])
                    ->setDescription($data['comments'])
                    ->setUrl($this->sanitationHelper->normalizeUrl($data['url']))
                ;

                if (null === $data['product_types']) {
                    return;
                }

                $types = explode('|', (string) $data['product_types']);

                foreach ($types as $type) {
                    if (null !== ($productType = $productTypeCache->fetch($type))) {
                        $competitor->addProductType($productType);
                    }
                }
            }, true
        );

        return 0;
    }
}

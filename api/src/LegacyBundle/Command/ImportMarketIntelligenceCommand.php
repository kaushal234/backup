<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use App\Entity\Sales\Competitor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Entity\Sales\ProductType;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:market_intelligences')]
class ImportMarketIntelligenceCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly SanitationHelper $sanitationHelper;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper)
    {
        parent::__construct();
        $this->setDescription('Imports MIM from legacy');
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
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $customerCache = $this->cacheFactory->createEntityCache(Customer::class, 'legacyId');
        $competitorCache = $this->cacheFactory->createEntityCache(Competitor::class, 'legacyId');
        $productTypeCache = $this->cacheFactory->createEntityCache(ProductType::class, 'legacyId');

        // Import market intelligence
        $sql = <<<'SQL'
            SELECT id, priv, typa, cuid, cor_id, type_id, status, dt, short_desc, dsca, poster
            FROM mim
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->progressiveImport(
            $output, $stmt, MarketIntelligence::class, 'legacyId', 'id',
            function (MarketIntelligence $marketIntelligence, array $data) use ($peopleCache, $customerCache, $competitorCache, $productTypeCache) {
                if (null !== $customerCache->fetch($data['cuid'])) {
                    $marketIntelligence->addCustomer($customerCache->fetch($data['cuid']));
                }
                if (null !== $competitorCache->fetch($data['cor_id'])) {
                    $marketIntelligence->addCompetitor($competitorCache->fetch($data['cor_id']));
                }
                if (null !== $productTypeCache->fetch($data['type_id'])) {
                    $marketIntelligence->addProductType($productTypeCache->fetch($data['type_id']));
                }
                $marketIntelligence
                    ->setCreatedAt(new \DateTime($data['dt']))
                    ->setPoster($peopleCache->fetch($data['poster']))
                    ->setDescription(mb_trim($this->sanitationHelper->parse($data['dsca'])))
                    ->setShortDescription(mb_trim($this->sanitationHelper->parse($data['short_desc'])))
                ;
            }, false
        );

        return 0;
    }
}

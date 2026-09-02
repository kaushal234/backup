<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use App\Entity\Sales\Competitor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\MarketIntelligence\MarketIntelligenceSubscription;
use App\Entity\Sales\ProductType;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:market_intelligence_subscriptions')]
class ImportMarketIntelligenceSubscriptionsCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory)
    {
        parent::__construct();
        $this->setDescription('Imports MIM Subscriptions from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $customerCache = $this->cacheFactory->createEntityCache(Customer::class, 'legacyId');
        $competitorCache = $this->cacheFactory->createEntityCache(Competitor::class, 'legacyId');
        $productTypeCache = $this->cacheFactory->createEntityCache(ProductType::class, 'legacyId');
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');

        // Import market intelligence
        $sql = <<<'SQL'
            SELECT id, typa, cuid, cor_id, type_id, poster
            FROM mim_not ORDER BY id ASC
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->progressiveImport(
            $output, $stmt, MarketIntelligenceSubscription::class, 'legacyId', 'id',
            static function (MarketIntelligenceSubscription $marketIntelligenceSubscription, array $data) use ($customerCache, $competitorCache, $productTypeCache, $peopleCache) {
                $marketIntelligenceSubscription
                    ->setSubscriber($peopleCache->fetch($data['poster']))
                    ->setCustomer($customerCache->fetch($data['cuid']))
                    ->setCompetitor($competitorCache->fetch($data['cor_id']))
                    ->setProductType($productTypeCache->fetch($data['type_id']))
                ;
            }, false,
            static fn (array $data) => null === $peopleCache->fetch($data['poster'])
        );

        return 0;
    }
}

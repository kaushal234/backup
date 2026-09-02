<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\Competitor;
use App\Entity\Sales\ForecastClosure;
use App\Entity\Sales\SalesForecast;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:fcr')]
class ImportForecastClosuresCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly SanitationHelper $sanitationHelper;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper)
    {
        parent::__construct();
        $this->setDescription('Imports FCR from legacy');
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
        $competitorCache = $this->cacheFactory->createEntityCache(Competitor::class, 'legacyId');
        $salesForecastCache = $this->cacheFactory->createEntityCache(SalesForecast::class, 'legacyId');

        $reasons = [
            'Customer Loyalty' => 'LOYALTY',
            'Lead-Time' => 'LEAD_TIME',
            'Technical / Equipment Performance' => 'PERFORMANCE',
            'Price' => 'PRICE',
            'Sales Job' => 'SALES',
            'Service & Spare Part Support' => 'SUPPORT',
            'Payment Terms' => 'PAYMENT',
            'Requirement Cancelled' => 'CANCELLED',
        ];

        // Import groups
        $sql = <<<'SQL'
            SELECT fcr.id, fcr.parent_id, fcr.sfr_status, fcr.sub_status, fcr.equote_id, fcr.ordered_qty, fcr.price, fcr.exwprice, fcr.currency, fcr.competitor, fcr.reason, fcr.comment, fcr.filename, created
            FROM fcr
            LEFT JOIN sfr ON fcr.parent_id = sfr.id
            WHERE sfr.id IS NOT NULL
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->progressiveImport(
            $output, $stmt, ForecastClosure::class, 'legacyId', 'id',
            function (ForecastClosure $forecastClosure, array $data) use ($salesForecastCache, $competitorCache, $reasons) {
                /** @var SalesForecast $salesForecast */
                $salesForecast = $salesForecastCache->fetch($data['parent_id']);

                $price = (float) $data['price'];
                $forecastClosure
                    ->setPoster($salesForecast->getPoster())
                    ->setStatus($data['sub_status'])
                    ->setCreatedAt(new \DateTime($data['created']))
                    ->setCompetitor($competitorCache->fetch($data['competitor']))
                    ->setPrice($price > 0 ? $price : null)
                    ->setBaanCurrency('' !== $data['currency'] ? $data['currency'] : null)
                    ->setOrderedQuantity((int) $data['ordered_qty'])
                    ->setReason($reasons[$data['reason']])
                    ->setSalesForecast($salesForecast)
                    ->setComment($this->sanitationHelper->parse($data['comment']))
                ;
            }, true
        );

        return 0;
    }
}

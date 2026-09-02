<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Module\Module;
use App\Entity\Sales\Competitor;
use App\Entity\Sales\CompetitorPricing;
use App\Entity\Sales\ForecastClosure;
use App\Repository\Module\ModuleRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:cpr')]
class ImportCompetitorPricingsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    private readonly ManagerRegistry $registry;

    private readonly SanitationHelper $sanitationHelper;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, ManagerRegistry $registry, SanitationHelper $sanitationHelper)
    {
        parent::__construct();
        $this->setDescription('Imports CPR from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->registry = $registry;
        $this->sanitationHelper = $sanitationHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $competitorCache = $this->cacheFactory->createEntityCache(Competitor::class, 'legacyId');
        $forecastClosureCache = $this->cacheFactory->createEntityCache(ForecastClosure::class, 'legacyId');

        // Import groups
        $sql = <<<'SQL'
            SELECT id, parent_id, qdate, competitor, model, options, qty, price, currency, exchange_rate, inco_terms, inco_loc, markup_percent, created
            FROM cpr
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        /** @var ModuleRepository $moduleRepository */
        $moduleRepository = $this->registry->getManager()->getRepository(Module::class);

        /** @var Module $fcrModule */
        $fcrModule = $moduleRepository->findByName('CPR');
        $moo = $fcrModule->getOperationalOwner();

        $this->helper->progressiveImport(
            $output, $stmt, CompetitorPricing::class, 'legacyId', 'id',
            function (CompetitorPricing $competitorPricing, array $data) use ($forecastClosureCache, $competitorCache, $moo) {
                /** @var ForecastClosure|null $forecastClosure */
                $forecastClosure = $forecastClosureCache->fetch($data['parent_id']);

                $price = (float) $data['price'];
                $quantity = (int) $data['qty'];
                $markup = (int) $data['markup_percent'];
                $competitorPricing
                    ->setPoster(null !== $forecastClosure ? $forecastClosure->getPoster() : $moo)
                    ->setPrice($price > 0 ? $price : null)
                    ->setBaanCurrency('' === $data['currency'] ? null : $data['currency'])
                    ->setCompetitor($competitorCache->fetch($data['competitor']))
                    ->setCreatedAt(new \DateTime($data['created']))
                    ->setQuotationDate(new \DateTime($data['qdate']))
                    ->setQuantity(0 === $quantity ? null : $quantity)
                    ->setCompetitorModel('' === $data['model'] ? null : $this->sanitationHelper->parse($data['model']))
                    ->setCompetitorOptions('' === $data['options'] ? null : $this->sanitationHelper->parse($data['options']))
                    ->setForecastClosure($forecastClosure)
                    ->setExchangeRate((float) $data['exchange_rate'])
                    ->setIncoterms('' === $data['inco_terms'] ? null : $data['inco_terms'])
                    ->setIncotermsLocation('' === $data['inco_loc'] ? null : $this->sanitationHelper->parse($data['inco_loc']))
                    ->setMarkupPercentage(0 === $markup ? null : $markup)
                ;
            }, true
        );

        return 0;
    }
}

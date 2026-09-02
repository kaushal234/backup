<?php

declare(strict_types=1);

namespace App\Command\Purchasing\SupplierRanking;

use App\Doctrine\EventListener\ActivityListener;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Finance\Currency;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Manager\Purchasing\SupplierRanking\SupplierRankingManager;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Blameable\BlameableListener;
use Gedmo\Timestampable\TimestampableListener;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:supplier_ranking:update_from_ln')]
class SupplierRankingUpdateCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;

    private readonly SupplierRankingManager $supplierRankingManager;

    private readonly EntityCacheHelperFactory $cacheHelperFactory;

    public function __construct(EntityManagerInterface $entityManager, SupplierRankingManager $supplierRankingManager, EntityCacheHelperFactory $cacheHelperFactory)
    {
        parent::__construct();
        $this->setDescription('Update supplier ranking revenue with LN datas');

        $this->entityManager = $entityManager;
        $this->supplierRankingManager = $supplierRankingManager;
        $this->cacheHelperFactory = $cacheHelperFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currencyCache = $this->cacheHelperFactory->createEntityCache(Currency::class, 'name');
        $countryCache = $this->cacheHelperFactory->createEntityCache(Country::class, 'isoCode2');

        $this->removeGedmoListener();
        $this->clearRevenue();

        // Find all locations
        foreach ($this->entityManager->getRepository(Location::class)->findAll() as $location) {
            if (null !== $location->getErp()) {
                // Get supplier turnover for each location
                foreach ($this->supplierRankingManager->findTurnoverWithSite($location->getErp()) as $turnover) {
                    $supplierRanking = $this->entityManager->getRepository(SupplierRanking::class)->createQueryBuilder('sr')
                        ->innerJoin('sr.supplier', 'supplier')
                        ->where('supplier.code = :supplierNumber')
                        ->setParameter('supplierNumber', $turnover->code)
                        ->getQuery()->getOneOrNullResult();

                    // Pass if not exist.
                    if (!$supplierRanking instanceof SupplierRanking) {
                        continue;
                    }

                    $supplierRanking->revenue = $turnover->turnover;
                }
            }
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }

    /**
     * Remove gedmo listener that update last review date automatically.
     * This property should only be used when user update supplier ranking.
     */
    protected function removeGedmoListener(): void
    {
        $eventManager = $this->entityManager->getEventManager();
        foreach ($eventManager->getListeners('onFlush') as $listener) {
            if ($listener instanceof TimestampableListener || $listener instanceof ActivityListener) {
                $eventManager->removeEventSubscriber($listener);
            }
            if ($listener instanceof BlameableListener) {
                $eventManager->removeEventListener('onFlush', $listener);
            }
        }
    }

    /**
     * Clear all revenue data of supplier ranking.
     * Because if supplier has no revenue, it will not be in the txTurnover result.
     * And this will not update the supplier ranking in this command.
     */
    protected function clearRevenue(): void
    {
        $this->entityManager->createQueryBuilder()
            ->update(SupplierRanking::class, 'supplier_ranking')
            ->set('supplier_ranking.revenue', 'NULL')
            ->getQuery()
            ->execute();
    }
}

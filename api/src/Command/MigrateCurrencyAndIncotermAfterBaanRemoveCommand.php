<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Finance\Currency;
use App\Entity\Sales\CompetitorPricing;
use App\Entity\Sales\ForecastClosure;
use App\Entity\Sales\Incoterm;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:migrate:baan_remove', description: 'Migrate some enities information after BAAN removal')]
class MigrateCurrencyAndIncotermAfterBaanRemoveCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityCacheHelperFactory $cacheFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $incotermCachefactory = $this->cacheFactory->createEntityCache(Incoterm::class, 'code');
        $currencyCacheFactory = $this->cacheFactory->createEntityCache(Currency::class, 'name');

        $forecastClosureRepository = $this->entityManager->getRepository(ForecastClosure::class);
        $competitorPricingRepository = $this->entityManager->getRepository(CompetitorPricing::class);

        foreach ($forecastClosureRepository->findAll() as $forecastClosure) {
            if (null === $forecastClosure->getBaanCurrency()) {
                continue;
            }

            $currencyToMigrate = 'RMB' === $forecastClosure->getBaanCurrency() ? 'CNY' : $forecastClosure->getBaanCurrency();
            $forecastClosure->setCurrency($currencyCacheFactory->fetch($currencyToMigrate));
            $output->writeln(\sprintf('FCR#%s currency updated', $forecastClosure->getId()));
            $this->entityManager->persist($forecastClosure);
        }

        foreach ($competitorPricingRepository->findAll() as $competitorPricing) {
            if (null !== $competitorPricing->getBaanCurrency()) {
                $currencyToMigrate = 'RMB' === $competitorPricing->getBaanCurrency() ? 'CNY' : $competitorPricing->getBaanCurrency();

                $competitorPricing->setCurrency($currencyCacheFactory->fetch($currencyToMigrate));
                $output->writeln(\sprintf('CPR#%s currency updated', $competitorPricing->getId()));

                $this->entityManager->persist($competitorPricing);
            }

            if (null !== $competitorPricing->getIncoterms()) {
                $competitorPricing->setIncoterm($incotermCachefactory->fetch($competitorPricing->getIncoterms()));
                $output->writeln(\sprintf('CPR#%s incoterm updated', $competitorPricing->getId()));

                $this->entityManager->persist($competitorPricing);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Country;
use App\Entity\Directory\People;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales_areas')]
class ImportSalesAreasCommand extends Command
{
    private readonly Connection $legacyConnection;

    private readonly EntityManagerInterface $em;

    private readonly EntityCacheHelperFactory $cacheFactory;

    /**
     * ImportSalesAreasCommand constructor.
     */
    public function __construct(Connection $legacyConnection, EntityManagerInterface $em, EntityCacheHelperFactory $cacheFactory)
    {
        parent::__construct();
        $this->setDescription('Import TLD legacy Sales Areas');
        $this->legacyConnection = $legacyConnection;
        $this->em = $em;
        $this->cacheFactory = $cacheFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $countryCache = $this->cacheFactory->createEntityCache(Country::class, 'name');

        // Import customer types
        $sql = <<<'SQL'
            SELECT id, country, customer, rep
            FROM sales_areas
            GROUP by rep, country
            SQL;
        $results = $this->legacyConnection->fetchAllAssociative($sql);

        $pg = new ProgressBar($output, \count($results));

        foreach ($results as $salesArea) {
            $pg->advance();

            /** @var Country|null $country */
            if (null === ($country = $countryCache->fetch($salesArea['country']))) {
                if ("Korea, Democratic People\'s Republic Of" === $salesArea['country']) {
                    $country = $countryCache->fetch('Korea, Republic of');
                } else {
                    continue;
                }
            }

            /** @var People|null $asm */
            $asm = $peopleCache->fetch($salesArea['rep']);

            if (null !== $asm) {
                $country->addAsm($asm);
                $this->em->persist($country);
            }
        }

        $this->em->flush();

        $pg->finish();

        return 0;
    }
}

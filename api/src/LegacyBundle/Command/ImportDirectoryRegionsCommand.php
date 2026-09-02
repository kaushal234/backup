<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use App\Entity\Directory\Region;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:directory:regions')]
class ImportDirectoryRegionsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory)
    {
        parent::__construct();
        $this->setDescription('Imports regions from legacy divisions table');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');

        // Import business units
        $sql = <<<'SQL'
            SELECT tld_regions.*
            FROM tld_regions
            GROUP BY tld_regions.id
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Region::class, 'legacyId', 'id',
            static function (Region $region, array $data) use ($peopleCache) {
                $region
                    ->setLegacyId((int) $data['id'])
                    ->setName($data['division'])
                    ->setRepresentative($peopleCache->fetch($data['repid']))
                ;
            }
        );

        return 0;
    }
}

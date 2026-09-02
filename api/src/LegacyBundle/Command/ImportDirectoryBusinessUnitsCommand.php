<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:directory:business-units')]
class ImportDirectoryBusinessUnitsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    /**
     * ImportDirectoryBusinessUnitsCommand constructor.
     */
    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory)
    {
        parent::__construct();
        $this->setDescription('Imports business units from legacy locations table');
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
        $locationsCache = $this->cacheFactory->createEntityCache(Location::class, 'legacyId');

        // Import business units
        $sql = <<<'SQL'
            SELECT id, business_unit, repid
            FROM locations
            WHERE business_unit != "" AND hq != ''
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, BusinessUnit::class, 'legacyId', 'id',
            static function (BusinessUnit $businessUnit, array $data) use ($peopleCache, $locationsCache) {
                $businessUnit
                    ->setLegacyId((int) $data['id'])
                    ->setName($data['business_unit'])
                    ->setRepresentative($peopleCache->fetch($data['repid']))
                    ->setLocation($locationsCache->fetch($data['id']))
                ;
            }, true
        );

        return 0;
    }
}

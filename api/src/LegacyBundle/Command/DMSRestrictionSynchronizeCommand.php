<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\Department;
use App\Entity\Directory\Division;
use App\Entity\Directory\Position;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use App\Entity\DMS;
use App\Entity\DMSRestriction;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:synch:dms_restriction', description: 'Synchronize TLD DMS Restrictions')]
class DMSRestrictionSynchronizeCommand extends Command
{
    public function __construct(
        private readonly ImportHelper $importHelper,
        private readonly EntityCacheHelperFactory $cacheHelperFactory,
        private readonly Connection $legacyConnection
    ) {
        parent::__construct();
    }

    public function skipFilter(DMSRestriction $restriction, array $data)
    {
        return
            (('0' === $data['division_id'] && null === $restriction->division) || $restriction->division?->getLegacyId() === (int) $data['division_id'])
            && (('0' === $data['region_id'] && null === $restriction->region) || $restriction->region?->getLegacyId() === (int) $data['region_id'])
            && (('0' === $data['subdivision_id'] && null === $restriction->subDivision) || $restriction->subDivision?->getLegacyId() === (int) $data['subdivision_id'])
            && (('0' === $data['fct_id'] && null === $restriction->position) || $restriction->position?->getLegacyId() === (int) $data['fct_id'])
            && (('0' === $data['buid'] && null === $restriction->businessUnit) || $restriction->businessUnit?->getLegacyId() === (int) $data['buid'])
            && (('0' === $data['dpt_id'] && null === $restriction->department) || $restriction->department?->getLegacyId() === (int) $data['dpt_id'])
            && $restriction->dms->getLegacyId() === (int) $data['parent_id']
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $divisionCache = $this->cacheHelperFactory->createEntityCache(Division::class, 'legacyId');
        $regionCache = $this->cacheHelperFactory->createEntityCache(Region::class, 'legacyId');
        $subDivisionCache = $this->cacheHelperFactory->createEntityCache(SubDivision::class, 'legacyId');
        $positionCache = $this->cacheHelperFactory->createEntityCache(Position::class, 'legacyId');
        $departmentCache = $this->cacheHelperFactory->createEntityCache(Department::class, 'legacyId');
        $dmsCache = $this->cacheHelperFactory->createEntityCache(DMS::class, 'legacyId');

        // Import DMS
        $sql = <<<'SQL'
            SELECT id, parent_id, region_id, bu_id, dpt_id, fct_id, subdivision_id, division_id
            FROM mod_org
            WHERE module='DMS' and name='acl'
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->importHelper->progressiveImport(
            $output, $stmt, DMSRestriction::class, 'legacyId', 'id',
            static function (DMSRestriction $restriction, array $data) use ($divisionCache, $regionCache, $subDivisionCache, $positionCache, $departmentCache, $dmsCache) {
                $restriction->dms = '0' === $data['parent_id'] ? null : $dmsCache->fetch((string) $data['parent_id']);
                $restriction->division = '0' === $data['division_id'] ? null : $divisionCache->fetch((string) $data['division_id']);
                $restriction->region = '0' === $data['region_id'] ? null : $regionCache->fetch((string) $data['region_id']);
                $restriction->subDivision = '0' === $data['subdivision_id'] ? null : $subDivisionCache->fetch((string) $data['subdivision_id']);
                $restriction->position = '0' === $data['fct_id'] ? null : $positionCache->fetch((string) $data['fct_id']);
                $restriction->department = '0' === $data['dpt_id'] ? null : $departmentCache->fetch((string) $data['dpt_id']);
                $restriction->setLegacyId((int) $data['id']);

                if (null === $restriction->position
                    && null === $restriction->division
                    && null === $restriction->region
                    && null === $restriction->subDivision
                    && null === $restriction->department
                ) {
                    throw new \InvalidArgumentException('Restriction empty, it should not be imported.');
                }
            }, true,
            $this->skipFilter(...)
        );

        return 0;
    }
}

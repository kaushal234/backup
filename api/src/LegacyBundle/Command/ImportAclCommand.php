<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Acl;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Group;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:acl')]
class ImportAclCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    /**
     * ImportAclCommand constructor.
     */
    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory)
    {
        parent::__construct();
        $this->setDescription('Imports acl from legacy people_groups table');
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
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'erp');

        // Import groups
        $sql = <<<'SQL'
            SELECT DISTINCT UPPER(group_name) as group_name, description, id
            FROM people_groups_select
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $groups = $this->helper->progressiveImport(
            $output, $stmt, Group::class, 'name', 'group_name',
            static function (Group $group, array $data) {
                $group
                    ->setLegacyId((int) $data['id'])
                    ->setDescription($data['description'] ?: $data['group_name']);
            }, true
        );

        // Import acls
        $sql = <<<'SQL'
            SELECT UPPER(g.group_name) as group_name, g.id, l.erp, g.parent_id
            FROM people_groups g
            INNER JOIN people p ON p.id = g.parent_id
            LEFT JOIN locations l ON l.erp = g.level
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Acl::class, 'legacyId', 'id',
            static function (Acl $acl, array $data) use ($groups, $peopleCache, $locationCache) {
                $acl
                    ->setGroup($groups[$data['group_name']])
                    ->setUser($peopleCache->fetch($data['parent_id']))
                ;

                if ($data['erp']) {
                    $acl->setLocation($locationCache->fetch($data['erp']));
                } else {
                    $acl->setLocation(null);
                }
            }, true
        );

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use App\Entity\Module\Module;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:modules')]
class ImportModulesCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    private readonly SanitationHelper $sanitationHelper;

    /**
     * ImportModulesCommand constructor.
     */
    public function __construct(
        ImportHelper $helper,
        Connection $legacyConnection,
        EntityCacheHelperFactory $cacheFactory,
        SanitationHelper $sanitationHelper
    ) {
        parent::__construct();
        $this->setDescription('Import TLD legacy modules');
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
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');

        // Import modules
        $sql = <<<'SQL'
            SELECT id, module, oid, uid, dsc, note, user_guide_id, help_page_id, migrated
            FROM com_modules;
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Module::class, 'legacyId', 'id',
            function (Module $module, array $data) use ($peopleCache) {
                $module
                    ->setLegacyId((int) $data['id'])
                    ->setName($data['module'])
                    ->setOperationalOwner($peopleCache->fetch($data['oid']))
                    ->setMisOwner($peopleCache->fetch($data['uid']))
                    ->setShortDescription($this->sanitationHelper->parse($data['dsc']))
                    ->setFullDescription($this->sanitationHelper->parse($data['note'], true, true, true))
                    ->setDmsProcedureId('0' === $data['user_guide_id'] ? null : (int) $data['user_guide_id'])
                    ->setDmsHelpId('0' === $data['help_page_id'] ? null : (int) $data['help_page_id'])
                    ->setMigrated('1' === $data['migrated'])
                ;
            }, true
        );

        return 0;
    }
}

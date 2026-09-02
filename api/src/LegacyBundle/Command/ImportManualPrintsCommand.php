<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use App\Entity\Support\Manual;
use App\Entity\Support\ManualPrint;
use App\Entity\Support\ManualPrinter;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:equipment:manual:prints')]
class ImportManualPrintsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly SanitationHelper $sanitationHelper;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper)
    {
        parent::__construct();
        $this->setDescription('Imports Manual Prints from legacy and 2 printers');
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
        $manualCache = $this->cacheFactory->createEntityCache(Manual::class, 'legacyId');
        $manualPrinterCache = $this->cacheFactory->createEntityCache(ManualPrinter::class, 'email');
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');

        // import prints (ex manuals_downloads)
        $sql = <<<'SQL'
            SELECT *
            FROM manuals_downloads
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->progressiveImport(
            $output, $stmt, ManualPrint::class, 'legacyId', 'id',
            function (ManualPrint $manualPrint, array $data) use ($manualCache, $manualPrinterCache, $peopleCache) {
                $manual = $manualCache->fetch($data['parent_id']);
                if (null === $manual) {
                    throw new \InvalidArgumentException('manual not found');
                }
                $manualPrint->manual = $manual;
                $manualPrint->manualPrinter = 7549 === (int) $data['vendor_id'] ? $manualPrinterCache->fetch('webmaster@sylvain-garrigues.com') : $manualPrinterCache->fetch('djacobs@printmarkservices.com');
                $manualPrint->standard = (int) $data['std_manual'];
                $manualPrint->full = (int) $data['full_manual'];
                $manualPrint->extra = (int) $data['extra_cd'];
                $manualPrint->chapter5 = (int) $data['chapter_5'];
                $manualPrint->comment = $data['comment'] ? $this->sanitationHelper->parse($data['comment']) : null;
                $manualPrint->createdAt = new \DateTime($data['dt_entered']);
                $manualPrint->requestedDeliveryDate = new \DateTime($data['dt_delivery']);
                $manualPrint->createdBy = $peopleCache->fetch($data['poster_id']);
                $manualPrint->downloadedAt = new \DateTime($data['dt_delivery']); // fake data arbitrary chosen,
                $manualPrint->setLegacyId((int) $data['id']);
            }, true
        );

        return 0;
    }
}

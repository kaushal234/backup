<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Entity\Parts\Courier;
use App\Entity\Parts\Tracking;
use App\Repository\Module\ModuleRepository;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:tracking')]
class ImportTrackingCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly ModuleRepository $moduleRepository;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, ModuleRepository $moduleRepository)
    {
        parent::__construct();
        $this->setDescription('Import TLD legacy catalogue product families');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $courierCache = $this->cacheFactory->createEntityCache(Courier::class, 'name');
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'erp');

        /** @var Module $fcrModule */
        $fcrModule = $this->moduleRepository->findByName('sPR');
        /** @var People $moo */
        $moo = $fcrModule->getOperationalOwner();

        // Import customer types
        $sql = <<<'SQL'
            SELECT id, dt, erp, dino, courier, trno, trnoBAK
            FROM erp_dino_trno
            ORDER BY id DESC
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, Tracking::class, 'legacyId', 'id',
            static function (Tracking $tracking, array $data) use ($courierCache, $locationCache, $moo) {
                $realCourier = true;
                if (\in_array($data['courier'], [Tracking::AWB, Tracking::BOL], true)) {
                    $tracking->documentType = $data['courier'];
                    $realCourier = false;
                }
                if (!is_numeric($data['dino'])) {
                    throw new \InvalidArgumentException('packing slip is NaN');
                }
                if ('' === $data['trno']) {
                    throw new \InvalidArgumentException('Tracking slip is empty');
                }
                if ($realCourier && null === $courier = $courierCache->fetch($data['courier'])) {
                    throw new \InvalidArgumentException('courier not found');
                }
                if (null === $location = $locationCache->fetch($data['erp'])) {
                    throw new \InvalidArgumentException('location not found');
                }
                /* @var Location $location */
                $tracking->location = $location;
                /* @var Courier|null $courier */
                $tracking->courier = $courier ?? null;
                $tracking->packingSlip = (int) $data['dino'];
                $tracking->trackingNumber = '' !== $data['trnoBAK'] ? $data['trnoBAK'] : $data['trno'];
                $tracking->createdAt = new \DateTime($data['dt']);
                $tracking->createdBy = $moo;
                $tracking->setLegacyId((int) $data['id']);
            }, true
        );

        return 0;
    }
}

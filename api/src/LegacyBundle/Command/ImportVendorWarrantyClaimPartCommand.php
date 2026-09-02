<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Parts\VendorWarrantyClaimPart;
use App\Entity\Purchasing\VendorWarrantyClaim;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:purchasing:vwc_part')]
class ImportVendorWarrantyClaimPartCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly SanitationHelper $sanitationHelper;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports VWC from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->sanitationHelper = $sanitationHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import VWC Parts
        $sql = <<<'SQL'
                SELECT vwc_parts.id, vwc_parts.parent_id, vwc_parts.supply_it, vwc_parts.part_number, vwc_parts.sn, vwc_parts.vendor_pn, vwc_parts.vendor_sn, vwc_parts.part_description, vwc_parts.quantity, vwc_parts.rec_qty, vwc_parts.um, vwc_parts.failure_type, vwc_parts.failure_system
                FROM vwc_parts
                LEFT JOIN vwc on vwc_parts.parent_id = vwc.id
                WHERE vwc.entered_by != 0 AND vwc.erp != 0 AND vwc.status != 'CLOSED' AND vwc.parent_id != 0
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $vwcCache = $this->cacheFactory->createEntityCache(VendorWarrantyClaim::class, 'id');

        $this->helper->disableValidation();
        $this->helper->setBatchSize(10_000);

        $this->helper->progressiveImport(
            $output, $stmt, VendorWarrantyClaimPart::class, 'id', 'id',
            function (VendorWarrantyClaimPart $part, array $data) use ($vwcCache) {
                $vendorWarrantyClaim = $vwcCache->fetch($data['parent_id']);
                if (!$vendorWarrantyClaim instanceof VendorWarrantyClaim) {
                    throw new \InvalidArgumentException('VWC not found');
                }

                $part->vendorWarrantyClaim = $vendorWarrantyClaim;
                $part->createdAt = $vendorWarrantyClaim->createdAt;
                $part->createdBy = $vendorWarrantyClaim->poster;
                $part->partNumber = mb_trim($this->sanitationHelper->parse($data['part_number']));
                $part->quantity = (float) $data['quantity'];
                $part->description = mb_trim($this->sanitationHelper->parse($data['part_description']));
                $part->unitOfMeasure = '' !== $data['um'] ? $data['um'] : 'EA';
                $part->failureSystem = '' !== $data['failure_system'] ? $data['failure_system'] : null;
                $part->failureType = '' !== $data['failure_type'] ? $data['failure_type'] : null;
                $part->vendorPartNumber = '' !== $data['vendor_pn'] ? $data['vendor_pn'] : null;
                $part->vendorSerialNumber = '' !== $data['vendor_sn'] ? $data['vendor_sn'] : null;
                $part->ship = ('YES' === $data['supply_it']);
            }, false
        );

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Entity\Purchasing\VendorWarrantyClaimType;
use App\Entity\Purchasing\WCVendorWarrantyClaim;
use App\Entity\Quality\NonConformity;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:purchasing:vwc')]
class ImportVendorWarrantyClaimCommand extends Command
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
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'erp');
        $statusCache = $this->cacheFactory->createEntityCache(VendorWarrantyClaimStatus::class, 'name');
        $typeCache = $this->cacheFactory->createEntityCache(VendorWarrantyClaimType::class, 'name');
        $scarCache = $this->cacheFactory->createEntityCache(SupplierCorrectiveActionRequest::class, 'id');
        $ncrCache = $this->cacheFactory->createEntityCache(NonConformity::class, 'id');

        $this->helper->setBatchSize(10_000);

        foreach (['WC' => WCVendorWarrantyClaim::class, 'NCR' => NCRVendorWarrantyClaim::class] as $module => $class) {
            // Import VWC
            $sql = <<<'SQL'
                 SELECT id, type, module, parent_id, erp, entered_by, assignee, suno, status, date, req, req_crd_amt, scar, scarno, shp_nam, shp_trk_no, su_rma, su_crd_not, su_crd_amt, act_crd_amt, su_shp_inst, su_accepted, cost_break, sbdp, resolution, supplier_erp
                 FROM vwc
                 WHERE entered_by != 0 AND erp != 0 AND status != 'CLOSED' AND module = :module AND parent_id != 0
                SQL;
            $stmt = $this->legacyConnection->executeQuery($sql, ['module' => $module]);

            $this->helper->disableValidation();
            $this->helper->progressiveImport(
                $output, $stmt, $class, 'id', 'id',
                function (VendorWarrantyClaim $vendorWarrantyClaim, array $data) use ($peopleCache, $locationCache, $statusCache, $typeCache, $scarCache, $ncrCache, $class, $output) {
                    $metadata = $this->entityManager->getClassMetaData($class);
                    $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
                    $metadata->setIdGenerator(new AssignedGenerator());

                    $metadata = $this->entityManager->getClassMetaData(VendorWarrantyClaim::class);
                    $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
                    $metadata->setIdGenerator(new AssignedGenerator());

                    $reflectionClass = new \ReflectionClass($class);
                    $reflectionProperty = $reflectionClass->getParentClass()->getProperty('id');
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($vendorWarrantyClaim, $data['id']);

                    if ($vendorWarrantyClaim instanceof WCVendorWarrantyClaim) {
                        $vendorWarrantyClaim->warrantyClaimId = (int) $data['parent_id'];
                    }

                    if ($vendorWarrantyClaim instanceof NCRVendorWarrantyClaim) {
                        if (null === ($nonConformity = $ncrCache->fetch($data['parent_id']))) {
                            $output->writeln(\sprintf('<error>VWC#%s not imported because NCR linked not found</error>', $data['id']));
                            throw new \InvalidArgumentException('Non Conformity not found');
                        }
                        $vendorWarrantyClaim->nonConformity = $nonConformity;
                    }

                    if (null === ($poster = $peopleCache->fetch($data['entered_by']))) {
                        $output->writeln(\sprintf('<error>VWC#%s not imported because poster not found</error>', $data['id']));
                        throw new \InvalidArgumentException('Poster not found');
                    }

                    $vendorWarrantyClaim->poster = $poster;
                    $vendorWarrantyClaim->location = $locationCache->fetch($data['erp']);
                    $vendorWarrantyClaim->createdAt = null !== $data['date'] ? new \DateTime($data['date']) : new \DateTime();
                    $vendorWarrantyClaim->status = $statusCache->fetch($data['status']);
                    $vendorWarrantyClaim->type = $typeCache->fetch($data['type']);
                    if (0 !== (int) $data['assignee']) {
                        $vendorWarrantyClaim->assignee = $peopleCache->fetch($data['assignee']);
                    }

                    $vendorWarrantyClaim->requestedCreditAmount = (float) $data['req_crd_amt'];
                    $vendorWarrantyClaim->scarRequested = ('Y' === $data['scar']);
                    $vendorWarrantyClaim->supplierCorrectiveActionRequest = $scarCache->fetch($data['scarno']);
                    $vendorWarrantyClaim->accepted = ('Y' === $data['su_accepted']);
                    $vendorWarrantyClaim->trackingNumber = '' !== $data['shp_trk_no'] ? $data['shp_trk_no'] : null;
                    $vendorWarrantyClaim->supplierShippingInstruction = '' !== $data['su_shp_inst'] ? mb_trim($this->sanitationHelper->parse($data['su_shp_inst'])) : null;
                    $vendorWarrantyClaim->supplierShipperName = '' !== $data['shp_nam'] ? $data['shp_nam'] : null;
                    $vendorWarrantyClaim->supplierReturnMerchandiseAuthorization = '' !== $data['su_rma'] ? $data['su_rma'] : null;
                    $vendorWarrantyClaim->supplierCreditNote = '' !== $data['su_crd_not'] ? $data['su_crd_not'] : null;
                    $vendorWarrantyClaim->supplierCreditAmount = (float) $data['su_crd_amt'];
                    $vendorWarrantyClaim->actualCreditAmount = (float) $data['act_crd_amt'];
                    $vendorWarrantyClaim->shipBackDefectivePart = ('Y' === $data['sbdp']);
                    $vendorWarrantyClaim->resolution = '' !== $data['resolution'] ? $data['resolution'] : null;
                    $vendorWarrantyClaim->requestedSupplierAction = '' !== $data['req'] ? mb_trim($this->sanitationHelper->parse($data['req'])) : '';
                    $vendorWarrantyClaim->costBreakdown = null !== $data['cost_break'] ? mb_trim($this->sanitationHelper->parse($data['cost_break'])) : null;

                    $vendorWarrantyClaim->setSupplierNumber('' !== $data['suno'] ? $data['suno'] : null);
                }, false
            );
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

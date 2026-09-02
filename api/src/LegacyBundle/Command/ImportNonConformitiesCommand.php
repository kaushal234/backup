<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Finance\Currency;
use App\Entity\Quality\NonConformity;
use App\Entity\Sales\Product;
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

#[AsCommand(name: 'legacy:import:quality:ncr')]
class ImportNonConformitiesCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly SanitationHelper $sanitationHelper;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports NCR from legacy');
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
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'legacyId');
        $productCache = $this->cacheFactory->createEntityCache(Product::class, 'name');
        $currencyCache = $this->cacheFactory->createEntityCache(Currency::class, 'name');

        // Import NCR
        $sql = <<<'SQL'
             SELECT id, factory, date, status, hours, t_emno, model, problem, short_desc, solution, responsible, vendor_erp, vendor_id, vendor_name, po_num, rush, charge_vendor, failure_type, ifactor, investigation, scrap, rework, FAI, use_as_is, derogation, return_vendor, charge_vendor4repairs, scar, car, other, comments, repair_approver, repair_sig_date, cost_cur, cost_total, cost_break, cost_ref, non_quality_cost, t_ninv, containment, processcode
             FROM ncr
             WHERE t_emno != 0 && factory != 0
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->disableValidation();
        $this->helper->progressiveImport(
            $output, $stmt, NonConformity::class, 'id', 'id',
            function (NonConformity $nonConformity, array $data) use ($peopleCache, $locationCache, $productCache, $currencyCache) {
                $metadata = $this->entityManager->getClassMetaData(NonConformity::class);
                $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
                $metadata->setIdGenerator(new AssignedGenerator());

                $reflectionProperty = new \ReflectionProperty(NonConformity::class, 'id');
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($nonConformity, $data['id']);

                $nonConformity->reportedBy = $peopleCache->fetch($data['t_emno']);
                $nonConformity->location = $locationCache->fetch($data['factory']);

                $nonConformity->createdAt = null !== $data['date'] ? new \DateTime($data['date']) : new \DateTime();
                $nonConformity->status = $data['status'];
                $nonConformity->hours = (int) $data['hours'];
                $nonConformity->problem = mb_trim($this->sanitationHelper->parse($data['problem']));
                $nonConformity->shortDescription = mb_trim($this->sanitationHelper->parse($data['short_desc']));
                $nonConformity->solution = mb_trim($this->sanitationHelper->parse($data['solution']));
                if ('' !== $data['po_num']) {
                    $nonConformity->purchaseOrderNumber = mb_trim($this->sanitationHelper->parse($data['po_num']));
                }
                $nonConformity->rush = ('Y' === $data['rush']);
                $nonConformity->chargeVendor = ('Y' === $data['charge_vendor']);
                if ('' !== $data['failure_type']) {
                    if ('Electical' === $data['failure_type']) {
                        $data['failure_type'] = 'Electrical';
                    }
                    $nonConformity->failureType = mb_trim($this->sanitationHelper->parse($data['failure_type']));
                }
                $nonConformity->iFactor = \sprintf('IF%s', '' !== $data['ifactor'] ? $data['ifactor'] : '1');
                $nonConformity->investigation = mb_trim($this->sanitationHelper->parse($data['investigation']));
                $nonConformity->scrap = ('Y' === $data['scrap']);
                $nonConformity->rework = ('Y' === $data['rework']);
                $nonConformity->firstArticleInspection = ('Y' === $data['FAI']);
                $nonConformity->useAsIs = ('Y' === $data['use_as_is']);
                $nonConformity->derogation = ('Y' === $data['derogation']);
                $nonConformity->returnVendor = ('Y' === $data['return_vendor']);
                $nonConformity->chargeVendorForRepair = ('Y' === $data['charge_vendor4repairs']);
                $nonConformity->supplierCorrectiveActionRequest = ('Y' === $data['scar']);
                $nonConformity->internalCorrectiveActionRequest = ('Y' === $data['car']);
                if (0 !== (int) $data['cost_total']) {
                    $nonConformity->cost = (float) $data['cost_total'];
                }
                $nonConformity->other = ('Y' === $data['other']);
                if ('' !== $data['comments']) {
                    $nonConformity->actionComment = mb_trim($this->sanitationHelper->parse($data['comments']));
                }
                if ('' !== $data['repair_approver']) {
                    $nonConformity->repairApprover = $peopleCache->fetch($data['repair_approver']);
                }
                if (null !== $data['repair_sig_date']) {
                    $nonConformity->repairApprovalDate = new \DateTime($data['repair_sig_date']);
                }
                if ('' !== $data['cost_cur']) {
                    $nonConformity->currency = $currencyCache->fetch($data['cost_cur']);
                }
                if ('' !== $data['cost_break']) {
                    $nonConformity->costBreakdown = mb_trim($this->sanitationHelper->parse($data['cost_break']));
                }
                if (0 !== (int) $data['non_quality_cost']) {
                    $nonConformity->nonQualityCost = (float) $data['non_quality_cost'];
                }
                if ('' !== $data['t_ninv']) {
                    $nonConformity->invoiceNumber = mb_trim($this->sanitationHelper->parse($data['t_ninv']));
                }

                $nonConformity->containment = ('Y' === $data['containment']);

                if (null !== ($product = $productCache->fetch($data['model']))) {
                    $nonConformity->addProduct($product);
                }

                if ('ENV' === $data['model']) {
                    $nonConformity->environmentalIssue = true;
                }

                if ('' !== $data['vendor_id']) {
                    $nonConformity->setSupplierNumber($data['vendor_id']);
                }

                if ('' !== $data['vendor_name']) {
                    $nonConformity->setSupplierName($data['vendor_name']);
                }
            }, false
        );

        return Command::SUCCESS;
    }
}

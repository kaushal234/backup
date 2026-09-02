<?php

declare(strict_types=1);

namespace LegacyBundle\Command\EquipmentRecordGroup;

use App\Entity\Common\Airport;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\EmissionRating;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Order;
use App\Entity\Sales\Product;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use LegacyBundle\Doctrine\Voter\SynchronizationVoter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'legacy:er:group:sync:equipments',
    description: 'Synchronize TLD legacy equipments records with api'
)]
class EquipmentRecordSyncCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ImportHelper $importHelper,
        private readonly SanitationHelper $sanitationHelper,
        private readonly EntityCacheHelperFactory $cacheHelperFactory,
        private readonly Connection $legacyConnection,
        private readonly SynchronizationVoter $synchronizationVoter
    ) {
        parent::__construct();
    }

    public function skipFilter(EquipmentRecord $equipment, array $data): bool
    {
        if (null !== $airport = $equipment->getAirport()) {
            $airportIdentical = $airport->getCode() === $data['airport_code'];
        } else {
            $airportIdentical = '' === $data['airport_code'];
        }

        if (null !== $manufacturerLocation = $equipment->getManufacturerLocation()) {
            $manufacturerLocationIdentical = $manufacturerLocation->getName() === $data['man_location'];
        } else {
            $manufacturerLocationIdentical = '' === $data['man_location'];
        }

        if (null !== $salesOrganisation = $equipment->getSalesOrganisation()) {
            $salesOrganisation = $salesOrganisation->getName() === $data['sales_org'];
        } else {
            $salesOrganisation = '' === $data['sales_org'];
        }

        if (null !== ($salesOrganisationService = $equipment->getSalesOrganisationService())) {
            $salesOrganisationService = $salesOrganisationService->getName() === $data['sso_service'];
        } else {
            $salesOrganisationService = '' === $data['sso_service'];
        }

        if (null !== $country = $equipment->getDeliveredCountry()) {
            $country = $country->getName() === $data['del_ctry'];
        } else {
            $country = '' === $data['del_ctry'];
        }

        if (null !== $emissionRating = $equipment->getEmissionRating()) {
            $emissionRating = $emissionRating->getName() === $data['eng_tier'];
        } else {
            $emissionRating = '' === $data['eng_tier'];
        }

        return $equipment->getSerialNumber() === $data['sn']
                && (('0' === $data['customer_id'] && null === $equipment->getEndUser()) || $equipment->getEndUser()?->getLegacyId() === (int) $data['customer_id'])
                && (('0' === $data['buyer_customer_id'] && null === $equipment->getBuyer()) || $equipment->getBuyer()?->getLegacyId() === (int) $data['buyer_customer_id'])
                && ($equipment->getMaintainer()?->getLegacyId() === $data['maintainer_customer_id'])
                && $airportIdentical
                && $manufacturerLocationIdentical
                && $salesOrganisation
                && $salesOrganisationService
                && $equipment->getModel() === $this->sanitationHelper->trimAndNullify($data['model'])
                && (('' === mb_trim((string) $data['model']) && null === $equipment->getProduct())
                || (null !== $equipment->getProduct() && $equipment->getProduct()->getName() === mb_trim((string) $data['model'])))
                && $equipment->getType() === $this->sanitationHelper->trimAndNullify($data['type'])
                && $equipment->getLocation() === $this->sanitationHelper->trimAndNullify($this->sanitationHelper->parse($data['location_short']))
                && $this->compareDate($equipment->getDateShipped(), $data['date_shipped'])
                && $this->compareDate($equipment->getDateCommissioned(), $data['dt_commissioned'])
                && $this->compareDate($equipment->getEstimatedGreenTagDate(), $data['dgt_rev'])
                && $this->compareDate($equipment->getFirstEstimatedGreenTagDate(), $data['first_estimated_green_tag_date'])
                && $this->compareDate($equipment->getGreenTagDate(), $data['dgt_act'])
                && $this->compareDate($equipment->getYellowTagDate(), $data['dyt'])
                && ($equipment->getOdpComment() === $this->sanitationHelper->trimAndNullify($data['odp_note']))
                && ($equipment->getFactoryComment() === $this->sanitationHelper->trimAndNullify($data['factory_comment']))
                && ($equipment->getProjectNumber() === $this->sanitationHelper->trimAndNullify($data['t_prno']))
                && ($equipment->getOptionsDescription() === $this->sanitationHelper->trimAndNullify($data['options_desc']))
                && (('0' === $data['diml'] && null === $equipment->getLength()) || ($equipment->getLength() === (int) $data['diml']))
                && (('0' === $data['dimw'] && null === $equipment->getWidth()) || ($equipment->getWidth() === (int) $data['dimw']))
                && (('0' === $data['dimh'] && null === $equipment->getHeight()) || ($equipment->getHeight() === (int) $data['dimh']))
                && (('0' === $data['dimk'] && null === $equipment->getWeight()) || ($equipment->getWeight() === (int) $data['dimk']))
                && ((null !== $order = $equipment->getOrder()) ? $order->getLegacyId() : 0) === (int) $data['sor_id']
                && ((bool) $data['publishable'] === $equipment->isPublishable())
                && $country
                && $emissionRating
                && (null === $equipment->getWorkOrder() || $equipment->getWorkOrder() === (string) $data['t_pdno'])
                && $this->compareDate($equipment->getFirstGreenTagDate(), $data['dgt_com'])
                && $this->compareDate($equipment->getWarrantyEndDate(), $data['date_warranty_end'])
                && ($equipment->getWarrantyConditions() === $this->sanitationHelper->trimAndNullify($data['warranty_conditions']))
                && ((bool) $data['light'] === $equipment->isLight())
                && ($equipment->getCombinationMode() === $this->sanitationHelper->trimAndNullify($data['comb_mod']))
                && ($equipment->getContractFMS() === $this->sanitationHelper->trimAndNullify($data['maintenance_contract_ref']))
                && ((bool) $data['tld_link'] === $equipment->isTldLink())
                && (('ACTIVE' === (string) $data['sim_status']) === $equipment->isSimCardStatusActive())
                && ((int) $data['fms_contract_length'] === $equipment->getFmsContractLength())
                && $this->compareDate($equipment->getFmsEndUseDate(), $data['fms_end_use_date'])
                && ((null !== $orderUnits = $equipment->orderFactory) ? $orderUnits->getLegacyId() : 0) === (int) $data['sor_uid']
                && (('0' === $data['hours'] && null === $equipment->getHourMeter()) || ($equipment->getHourMeter() === (int) $data['hours']))
                && $this->compareDate($equipment->getLastCBOMUpdateDate(), $data['last_cbom_update_date'])
                && ($data['state'] === $equipment->getState())
                && ($data['status'] === $equipment->getStatus())
                && $this->compareDate($equipment->getActualDeliveryDate(), $data['ddel_act2'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->synchronizationVoter->disable();

        $this->em->getFilters()->disable('softdeleteable');

        $customerCache = $this->cacheHelperFactory->createEntityCache(Customer::class, 'legacyId');
        $productCache = $this->cacheHelperFactory->createEntityCache(Product::class, 'name');
        $locationCache = $this->cacheHelperFactory->createEntityCache(Location::class, 'name');
        $orderCache = $this->cacheHelperFactory->createEntityCache(Order::class, 'legacyId');
        $airportCache = $this->cacheHelperFactory->createFirstOrNullEntityCache(Airport::class, 'code');
        $countryCache = $this->cacheHelperFactory->createFirstOrNullEntityCache(Country::class, 'name');
        $emissionRatingCache = $this->cacheHelperFactory->createFirstOrNullEntityCache(EmissionRating::class, 'name');
        $sanitationHelper = $this->sanitationHelper;

        $sql = <<<'SQL'
            SELECT er.id,
                   er.sn,
                   er.customer_id,
                   er.buyer_customer_id,
                   er.maintainer_customer_id,
                   er.cust_asset_num,
                   er.model,
                   er.type,
                   er.airport_code,
                   er.location_short,
                   er.man_location,
                   er.sales_org,
                   er.sso_service,
                   IF(er.date_shipped = '0000-00-00 00:00:00', NULL, er.date_shipped) as date_shipped,
                   IF(er.dt_commissioned = '0000-00-00 00:00:00', NULL, er.dt_commissioned) as dt_commissioned,
                   IF(er.dgt_rev = '0000-00-00 00:00:00', NULL, er.dgt_rev) as dgt_rev,
                   IF(er.first_estimated_green_tag_date = '0000-00-00 00:00:00', NULL, er.first_estimated_green_tag_date) as first_estimated_green_tag_date,
                   IF(er.date_warranty_end = '0000-00-00 00:00:00', NULL, er.date_warranty_end) as date_warranty_end,
                   er.warranty_conditions,
                   er.odp_note,
                   er.diml,
                   er.dimw,
                   er.dimh,
                   er.dimk,
                   er.factory_comment,
                   er.hours,
                   (SELECT sol.parent_id FROM sor_lines AS sol LEFT JOIN sor_units AS su ON su.parent_id=sol.id WHERE su.id = er.sor_uid LIMIT 1) AS sor_id,
                   IF(er.dgt_act = '0000-00-00 00:00:00', NULL, er.dgt_act) as dgt_act,
                   IF(er.dyt = '0000-00-00 00:00:00', NULL, er.dyt) as dyt,
                   IF(er.dgt_com = '0000-00-00 00:00:00', NULL, er.dgt_com) as dgt_com,
                   er.t_prno,
                   er.options_desc,
                   IF(er.publishable = 'Y', true, false) as publishable,
                   er.del_ctry,
                   er.t_pdno,
                   IF(er.dgt_com = '0000-00-00 00:00:00', NULL, er.dgt_com) as dgt_com,
                   IF(er.dyt = '0000-00-00 00:00:00', NULL, er.dyt) as dyt,
                   er.light,
                   er.eng_tier,
                   er.comb_mod,
                   er.maintenance_contract_ref,
                   er.tld_link,
                   er.sim_status,
                   er.fms_contract_length,
                   IF(er.fms_end_use_date = '0000-00-00 00:00:00', NULL, er.fms_end_use_date) as fms_end_use_date,
                   er.sor_uid,
                   er.last_cbom_update_date,
                   er.state,
                   er.status,
                   IF(er.ddel_act2 = '0000-00-00 00:00:00', NULL, er.ddel_act2) as ddel_act2
            FROM service er
            LEFT JOIN customers enduser ON customer_id = enduser.id
            LEFT JOIN customers buyer ON buyer_customer_id = buyer.id
            WHERE sn != 'PLEASE CHANGE'
            ORDER BY er.id DESC
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->importHelper->disableValidation();

        $this->importHelper->progressiveImport(
            $output, $stmt, EquipmentRecord::class, 'legacyId', 'id',
            static function (EquipmentRecord $equipment, array $data) use ($customerCache, $productCache, $airportCache, $sanitationHelper, $locationCache, $orderCache, $countryCache, $emissionRatingCache) {
                if ((null !== $equipment->orderFactory) && $equipment->orderFactory->getLegacyId() !== (int) $data['sor_uid']) {
                    $equipment->orderFactory->equipmentRecord = null;
                }

                $equipment
                    ->setSerialNumber($data['sn'])
                    ->setEndUser('0' === $data['customer_id'] ? null : $customerCache->fetch((string) $data['customer_id']))
                    ->setBuyer('0' === $data['buyer_customer_id'] ? null : $customerCache->fetch((string) $data['buyer_customer_id']))
                    ->setMaintainer($customerCache->fetch((string) $data['maintainer_customer_id']))
                    ->setCustomerSerialNumber($sanitationHelper->trimAndNullify($data['cust_asset_num']))
                    ->setModel($sanitationHelper->trimAndNullify($data['model']))
                    ->setProduct($productCache->fetch((string) $data['model']))
                    ->setType($sanitationHelper->trimAndNullify($data['type']))
                    ->setDateShipped(null === $data['date_shipped'] ? null : new \DateTime($data['date_shipped']))
                    ->setDateCommissioned(null === $data['dt_commissioned'] ? null : new \DateTime($data['dt_commissioned']))
                    ->setLocation($sanitationHelper->trimAndNullify($sanitationHelper->parse($data['location_short'])))
                    ->setAirport($airportCache->fetch((string) $data['airport_code']))
                    ->setManufacturerLocation($locationCache->fetch((string) $data['man_location']))
                    ->setSalesOrganisation($locationCache->fetch((string) $data['sales_org']))
                    ->setSalesOrganisationService($locationCache->fetch((string) $data['sso_service']))
                    ->setEstimatedGreenTagDate(null === $data['dgt_rev'] ? null : new \DateTime($data['dgt_rev']))
                    ->setFirstEstimatedGreenTagDate(null === $data['first_estimated_green_tag_date'] ? null : new \DateTime($data['first_estimated_green_tag_date']))
                    ->setWarrantyEndDate(null === $data['date_warranty_end'] ? null : new \DateTime($data['date_warranty_end']))
                    ->setWarrantyConditions($sanitationHelper->trimAndNullify($data['warranty_conditions']))
                    ->setOdpComment($sanitationHelper->trimAndNullify($data['odp_note']))
                    ->setFactoryComment($sanitationHelper->trimAndNullify($data['factory_comment']))
                    ->setLength('0' === $data['diml'] ? null : (int) $data['diml'])
                    ->setWidth('0' === $data['dimw'] ? null : (int) $data['dimw'])
                    ->setHeight('0' === $data['dimh'] ? null : (int) $data['dimh'])
                    ->setWeight('0' === $data['dimk'] ? null : (int) $data['dimk'])
                    ->setGreenTagDate(null === $data['dgt_act'] ? null : new \DateTime($data['dgt_act']))
                    ->setYellowTagDate(null === $data['dyt'] ? null : new \DateTime($data['dyt']))
                    ->setFirstGreenTagDate(null === $data['dgt_com'] ? null : new \DateTime($data['dgt_com']))
                    ->setProjectNumber($sanitationHelper->trimAndNullify($data['t_prno']))
                    ->setOptionsDescription($sanitationHelper->trimAndNullify($data['options_desc']))
                    ->setPublishable((bool) $data['publishable'])
                    ->setOrder($orderCache->fetch((string) $data['sor_id']))
                    ->setDeliveredCountry($countryCache->fetch((string) $data['del_ctry']))
                    ->setWorkOrder((string) $data['t_pdno'])
                    ->setFirstGreenTagDate(null === $data['dgt_com'] ? null : new \DateTime($data['dgt_com']))
                    ->setYellowTagDate(null === $data['dyt'] ? null : new \DateTime($data['dyt']))
                    ->setLight((bool) $data['light'])
                    ->setEmissionRating($emissionRatingCache->fetch((string) $data['eng_tier']))
                    ->setCombinationMode($sanitationHelper->trimAndNullify($data['comb_mod']))
                    ->setContractFMS($sanitationHelper->trimAndNullify($data['maintenance_contract_ref']))
                    ->setIsTldLink((bool) $data['tld_link'])
                    ->setSimCardStatusActive('ACTIVE' === (string) $data['sim_status'])
                    ->setFmsContractLength((int) $data['fms_contract_length'])
                    ->setFmsEndUseDate(null === $data['fms_end_use_date'] ? null : new \DateTime($data['fms_end_use_date']))
                    ->setHourMeter('0' === $data['hours'] ? null : (int) $data['hours'])
                    ->setLastCBOMUpdateDate(null === $data['last_cbom_update_date'] ? null : new \DateTime($data['last_cbom_update_date']))
                    ->setState((string) $data['state'])
                    ->setStatus((string) $data['status'])
                    ->setActualDeliveryDate(null === $data['ddel_act2'] ? null : new \DateTime($data['ddel_act2']))
                ;
            }, true,
            $this->skipFilter(...)
        );

        // Import customers parents
        $sql = <<<'SQL'
            SELECT er.id, er.parent_id
            FROM service er
            INNER JOIN service parent ON parent.id = er.parent_id
            INNER JOIN customers enduser ON er.customer_id = enduser.id
            INNER JOIN customers buyer ON er.buyer_customer_id = buyer.id
            INNER JOIN customers penduser ON parent.customer_id = penduser.id
            INNER JOIN customers pbuyer ON parent.buyer_customer_id = pbuyer.id
            WHERE
                er.parent_id > 0 AND  er.customer_id > 0 AND er.buyer_customer_id > 0  AND er.sn != 'PLEASE CHANGE'
                AND parent.customer_id > 0 AND parent.buyer_customer_id > 0  AND parent.sn != 'PLEASE CHANGE'
            SQL;
        $equipmentCache = $this->cacheHelperFactory->createEntityCache(EquipmentRecord::class, 'legacyId');
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->importHelper->progressiveImport(
            $output, $stmt, EquipmentRecord::class, 'legacyId', 'id',
            static function (EquipmentRecord $equipment, array $data) use ($equipmentCache) {
                $equipment
                    ->setParentEquipment($equipmentCache->fetch((string) $data['parent_id']))
                ;
            }, true,
            static fn (EquipmentRecord $equipment, array $data) => null !== $equipment->getParentEquipment() && $equipment->getParentEquipment()->getLegacyId() === (int) $data['parent_id']
        );

        return 0;
    }

    private function compareDate($apiDate, $legacyDate)
    {
        $apiDateFormatted = $apiDate instanceof \DateTime ? $apiDate->format('Y-m-d') : null;
        $legacyDateFormatted = null !== $legacyDate ? (new \DateTime($legacyDate))->format('Y-m-d') : null;

        return $apiDateFormatted === $legacyDateFormatted;
    }
}

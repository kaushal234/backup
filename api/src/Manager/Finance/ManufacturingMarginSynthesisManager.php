<?php

declare(strict_types=1);

namespace App\Manager\Finance;

use App\Dto\Finance\ManufacturingMarginSynthesis;
use Cake\Chronos\Chronos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ManufacturingMarginSynthesisManager
{
    private readonly EntityManagerInterface $entityManager;
    private readonly DenormalizerInterface $denormalizer;

    public function __construct(EntityManagerInterface $entityManager, DenormalizerInterface $denormalizer)
    {
        $this->entityManager = $entityManager;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @return array|ManufacturingMarginSynthesis[]
     */
    public function getManufacturingMarginSynthesisForPeriod(string $dateFrom, string $dateTo, int $factory): array
    {
        $sql = <<<'SQL'
            SELECT
                   financeFamily.id,
                   mfg.exported_at AS exportDate,
                   er_factory.name AS factory,
                   financeFamily.name AS financeFamily,
                   productType.english_name AS productType,
                   COUNT(er.id) AS quantity,
                   currency.name AS currency,
                   (SUM(mfg.factory_revenue) / COUNT(er.id)) DIV 1 AS averageTransferPrice,
                   ((SUM(mfg.factory_revenue * ((mfg.factory_revenue - mfg.actual_labour_cost - mfg.actual_material_cost - mfg.actual_other_material_cost - mfg.actual_other_direct_cost) / mfg.factory_revenue * 100))) / (SUM(mfg.factory_revenue))) DIV 1 AS averageActualDirectMargin,
                   (SUM(productManufacturing.mbh) / COUNT(er.id)) DIV 1 AS averageModelBaseHours,
                   (SUM(productManufacturing.mbh * productManufacturing.iip) / SUM(productManufacturing.mbh)) DIV 1 AS averageIndustrialIncorporationParameter,
                   (SUM(mfg.option_configuration_parameter_hours) / COUNT(er.id)) DIV 1 AS averageOptionConfigurationParameterHours,
                   (SUM(productManufacturing.mbh * (productManufacturing.iip / 100) + mfg.option_configuration_parameter_hours) / COUNT(er.id)) DIV 1 AS averageUnitAllocatedHours,
                   (SUM(mfg.actual_hours) / COUNT(er.id)) DIV 1 AS averageActualHours,
                   (SUM(productManufacturing.mbh * (productManufacturing.iip / 100) + mfg.option_configuration_parameter_hours) / SUM(mfg.actual_hours) * 100) DIV 1 AS averageFactoryStandardEfficiencyActual
            FROM manufacturing_margin mfg
                LEFT JOIN equipment_records er ON mfg.equipment_record_id = er.id
                LEFT JOIN directory_location er_factory ON er.manufacturer_location_id = er_factory.id
                LEFT JOIN products p ON er.product_id = p.id
                LEFT JOIN families financeFamily ON p.finance_family_id = financeFamily.id
                LEFT JOIN families productFamilyProduct ON p.family_id = productFamilyProduct.id
                LEFT JOIN product_families productFamilyType ON p.family_id = productFamilyType.id
                LEFT JOIN product_types productType ON productFamilyType.product_type_id = productType.id
                LEFT JOIN currencies currency ON mfg.currency_id = currency.id
                LEFT JOIN (SELECT industrial_incorporation_parameter AS iip, factory_standard_efficiency AS fse, model_base_hours AS mbh, product_id
                            FROM product_manufacturing pm
                            WHERE pm.factory_id = :factory
                              AND pm.effective_at >= :yearBeginning
                              AND pm.effective_at <= :yearEnd
                    ) productManufacturing ON productManufacturing.product_id = p.id
                WHERE mfg.exported_at >= :dateFrom AND mfg.exported_at <= :dateTo AND er.manufacturer_location_id = :factory AND p.finance_family_id IS NOT NULL
                GROUP BY financeFamily.id
            SQL;

        $statement = $this->entityManager->getConnection()->executeQuery($sql, [
            'factory' => $factory,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'yearBeginning' => Chronos::instance(new \DateTime($dateFrom))->startOfYear(),
            'yearEnd' => Chronos::instance(new \DateTime($dateFrom))->endOfYear(),
        ]);

        $results = [];
        foreach ($statement->fetchAllAssociative() as $row) {
            $results[] = $this->denormalizer->denormalize($row, ManufacturingMarginSynthesis::class);
        }

        return $results;
    }
}

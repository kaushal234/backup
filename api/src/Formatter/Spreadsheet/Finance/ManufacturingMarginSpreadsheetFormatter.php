<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Finance;

use App\Entity\Finance\ManufacturingMargin;
use App\Entity\Manufacturing\ProductManufacturing;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use LegacyBundle\Manager\ManufacturingMarginManager;
use Symfony\Component\HttpFoundation\RequestStack;

class ManufacturingMarginSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    private const FIRST_PRODUCT_MANUFACTURING_MARGINS_DATE = '2020-01-01';

    public function __construct(
        private readonly ManufacturingMarginManager $manager,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getColumnToRename(): array
    {
        return [
            'equipmentRecord.serialNumber' => 'equipment record',
            'equipmentRecord.product.financeFamily' => 'finance family',
            'equipmentRecord.model' => 'model',
            'equipmentRecord.emissionRating' => 'emission rating',
            'sol.customerUser' => 'customer user',
            'factoryRevenue' => 'tp',
            'estDirMarginPer' => 'proj dm (%)',
            'factoryStandardEfficiency' => 'fse actual',
            'unitAllocatedHours' => 'uah',
            'modelBaseHours' => 'mbh',
            'optionConfigurationParameterHours' => 'ocp hours',
            'industrialIncorporationParameter' => 'iip (%)',
            'equipmentRecord.manufacturerLocation' => 'factory',
            'equipmentRecord.type' => 'product type',
            'standardDirectMarginPercentage' => 'std dm (%)',
            'factoryStandardEfficiencyBudget' => 'fse budget',
            'actualDirectMarginPercentage' => 'act dm (%)',
            'exportedAt' => 'date',
        ];
    }

    public function getComputedColumns(): array
    {
        return ['factoryRevenue', 'factoryDiscount', 'actualDirectMarginPercentage', 'estDirMarginPer', 'modelBaseHours', 'industrialIncorporationParameter', 'factoryStandardEfficiency', 'unitAllocatedHours', 'actualHours', 'factoryStandardEfficiency', 'solId', 'sso', 'standardHours', 'factoryStandardEfficiencyBudget', 'targetHours', 'sol.customerUser', 'standardMaterialCost', 'actualMaterialCost', 'standardOtherDirectCost', 'actualOtherDirectCost'];
    }

    /**
     * @param ManufacturingMargin $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        $salesOrderLine = $this->manager->getSalesOrderLineInformation($item);

        $factoryStandardEfficiency = null;
        $industrialIncorporationParameter = null;
        $modelBaseHours = null;

        $equipmentRecord = $item->getEquipmentRecord();
        /** @var ProductManufacturing[]|ArrayCollection $productManufacturings */
        $productManufacturings = $equipmentRecord && $equipmentRecord->getProduct() ? $equipmentRecord->getProduct()->getProductManufacturings() : new ArrayCollection();

        $filterDate = $this->requestStack->getCurrentRequest()->query->all('exportedAt');
        $after = (!empty($filterDate['after']))
            ? new \DateTime($filterDate['after'])
            : new \DateTime(self::FIRST_PRODUCT_MANUFACTURING_MARGINS_DATE);

        foreach ($productManufacturings as $productManufacturing) {
            if ($after->format('Y') > $productManufacturing->getEffectiveAt()->format('Y')) {
                continue;
            }

            if (null !== $equipmentRecord && $equipmentRecord->getManufacturerLocation() === $productManufacturing->getFactory()) {
                $industrialIncorporationParameter = $productManufacturing->getIndustrialIncorporationParameter();
                $factoryStandardEfficiency = $productManufacturing->getFactoryStandardEfficiency();
                $modelBaseHours = $productManufacturing->getModelBaseHours();
            }
        }

        $unitAllocatedHours = null !== $industrialIncorporationParameter && null !== $item->getOptionConfigurationParameterHours() && null !== $modelBaseHours ? round(($modelBaseHours * ($industrialIncorporationParameter / 100)) + $item->getOptionConfigurationParameterHours()) : null;

        return match ($column) {
            'factoryRevenue' => round($item->getFactoryRevenue()),
            'factoryDiscount' => round($salesOrderLine['factory_discount']),
            'actualDirectMarginPercentage' => null !== $item->getActualDirectMarginPercentage() ? round($item->getActualDirectMarginPercentage()) : null,
            'estDirMarginPer' => null !== $salesOrderLine['est_dir_margin_per'] ? round($salesOrderLine['est_dir_margin_per']) : null,
            'modelBaseHours' => $modelBaseHours,
            'industrialIncorporationParameter' => $industrialIncorporationParameter,
            'unitAllocatedHours' => null !== $industrialIncorporationParameter && null !== $item->getOptionConfigurationParameterHours() && null !== $modelBaseHours ? round(($modelBaseHours * ($industrialIncorporationParameter / 100)) + $item->getOptionConfigurationParameterHours()) : null,
            'actualHours' => round($item->getActualHours()),
            'factoryStandardEfficiency' => $unitAllocatedHours && $item->getActualHours() ? round($unitAllocatedHours / $item->getActualHours() * 100) : null,
            'solId' => $salesOrderLine['sol_id'],
            'sso' => $salesOrderLine['sso_fullname'],
            'targetHours' => round($item->getStandardHours()),
            'factoryStandardEfficiencyBudget' => $factoryStandardEfficiency,
            'standardHours' => $unitAllocatedHours && $factoryStandardEfficiency ? round($unitAllocatedHours / $factoryStandardEfficiency * 100) : null,
            'sol.customerUser' => $salesOrderLine['user_customer'] ?? null,
            'standardMaterialCost' => round($item->getStandardMaterialCost()),
            'actualMaterialCost' => round($item->getActualMaterialCost()),
            'standardOtherDirectCost' => round($item->getStandardOtherDirectCost()),
            'actualOtherDirectCost' => round($item->getActualOtherDirectCost()),
            default => null,
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return ManufacturingMargin::class === $class;
    }
}

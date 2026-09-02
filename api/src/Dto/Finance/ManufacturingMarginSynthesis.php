<?php

declare(strict_types=1);

namespace App\Dto\Finance;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\DataProvider\ManufacturingMarginSynthesisDataProvider;
use App\Filter\ColumnsFilter;
use App\Filter\Finance\ManufacturingMarginSynthesisFilter;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            security: "is_granted('FEATURE_MANUFACTURING_MARGIN_VIEW_FULL') or is_granted('FEATURE_MANUFACTURING_MARGIN_VIEW_LOCATION')",
            provider: ManufacturingMarginSynthesisDataProvider::class
        ),
        new Get(),
    ],
    routePrefix: 'finance',
    normalizationContext: ['groups' => ['manufacturing_margin:synthesis'], 'datetime_format' => 'Y-m']
)]
#[ApiFilter(ManufacturingMarginSynthesisFilter::class)]
#[ApiFilter(ColumnsFilter::class)]
class ManufacturingMarginSynthesis
{
    /**
     * @var string
     */
    final public const DATE = 'Date';
    /**
     * @var string
     */
    final public const PRODUCT_TYPE = 'Product Type';
    /**
     * @var string
     */
    final public const FINANCE_FAMILY = 'Finance Family';
    /**
     * @var string
     */
    final public const QUANTITY = 'Qty';
    /**
     * @var string
     */
    final public const CURRENCY = 'Cur';
    /**
     * @var string
     */
    final public const AVERAGE_TRANSFER_PRICE = 'Av. Tp';
    /**
     * @var string
     */
    final public const AVERAGE_FACTORY_DISCOUNT = 'Av. Discount';
    /**
     * @var string
     */
    final public const AVERAGE_ACTUAL_DIRECT_MARGIN = 'Av. Act. Dm (%)';
    /**
     * @var string
     */
    final public const AVERAGE_PROJECTED_DIRECT_MARGIN = 'Av. Proj. Dm (%)';
    /**
     * @var string
     */
    final public const AVERAGE_MODEL_BASE_HOURS = 'Av. Mbh';
    /**
     * @var string
     */
    final public const AVERAGE_INDUSTRIAL_INCORPORATION_PARAMETER = 'Av. Iip (%)';
    /**
     * @var string
     */
    final public const AVERAGE_OPTION_CONFIGURATION_PARAMETER_HOURS = 'Av. Ocp Hours';
    /**
     * @var string
     */
    final public const AVERAGE_UNIT_ALLOCATED_HOURS = 'Av. Uah';
    /**
     * @var string
     */
    final public const AVERAGE_ACTUAL_HOURS = 'Av. Act. Hours';
    /**
     * @var string
     */
    final public const AVERAGE_FACTORY_STANDARD_EFFICIENCY = 'Fse Actual';

    #[ApiProperty(identifier: true)]
    private int $id;

    #[Groups(['manufacturing_margin:synthesis'])]
    private \DateTimeInterface $exportDate;

    #[Groups(['manufacturing_margin:synthesis'])]
    private string $financeFamily;

    #[Groups(['manufacturing_margin:synthesis'])]
    private string $factory;

    #[Groups(['manufacturing_margin:synthesis'])]
    private string $productType;

    #[Groups(['manufacturing_margin:synthesis'])]
    private int $quantity;

    #[Groups(['manufacturing_margin:synthesis'])]
    private string $currency;

    #[Groups(['manufacturing_margin:synthesis'])]
    private ?int $averageTransferPrice = null;

    #[Groups(['manufacturing_margin:synthesis'])]
    private ?int $averageFactoryDiscount = null;

    #[Groups(['manufacturing_margin:synthesis'])]
    private ?int $averageProjectedDirectMargin = null;

    #[Groups(['manufacturing_margin:synthesis'])]
    private ?int $averageActualDirectMargin = null;

    #[Groups(['manufacturing_margin:synthesis'])]
    private ?int $averageModelBaseHours = null;

    #[Groups(['manufacturing_margin:synthesis'])]
    private ?int $averageIndustrialIncorporationParameter = null;

    #[Groups(['manufacturing_margin:synthesis'])]
    private ?int $averageOptionConfigurationParameterHours = null;

    #[Groups(['manufacturing_margin:synthesis'])]
    private ?int $averageUnitAllocatedHours = null;

    #[Groups(['manufacturing_margin:synthesis'])]
    private ?int $averageActualHours = null;

    #[Groups(['manufacturing_margin:synthesis'])]
    private ?int $averageFactoryStandardEfficiencyActual = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getExportDate(): \DateTimeInterface
    {
        return $this->exportDate;
    }

    public function setExportDate(\DateTimeInterface $exportDate): self
    {
        $this->exportDate = $exportDate;

        return $this;
    }

    public function getFinanceFamily(): string
    {
        return $this->financeFamily;
    }

    public function setFinanceFamily(string $financeFamily): self
    {
        $this->financeFamily = $financeFamily;

        return $this;
    }

    public function getFactory(): string
    {
        return $this->factory;
    }

    public function setFactory(string $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    public function getProductType(): string
    {
        return $this->productType;
    }

    public function setProductType(string $productType): self
    {
        $this->productType = $productType;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getAverageTransferPrice(): ?int
    {
        return $this->averageTransferPrice;
    }

    public function setAverageTransferPrice(?int $averageTransferPrice): self
    {
        $this->averageTransferPrice = $averageTransferPrice;

        return $this;
    }

    public function getAverageFactoryDiscount(): ?int
    {
        return $this->averageFactoryDiscount;
    }

    public function setAverageFactoryDiscount(?int $averageFactoryDiscount): self
    {
        $this->averageFactoryDiscount = $averageFactoryDiscount;

        return $this;
    }

    public function getAverageProjectedDirectMargin(): ?int
    {
        return $this->averageProjectedDirectMargin;
    }

    public function setAverageProjectedDirectMargin(?int $averageProjectedDirectMargin): self
    {
        $this->averageProjectedDirectMargin = $averageProjectedDirectMargin;

        return $this;
    }

    public function getAverageActualDirectMargin(): ?int
    {
        return $this->averageActualDirectMargin;
    }

    public function setAverageActualDirectMargin(?int $averageActualDirectMargin): self
    {
        $this->averageActualDirectMargin = $averageActualDirectMargin;

        return $this;
    }

    public function getAverageModelBaseHours(): ?int
    {
        return $this->averageModelBaseHours;
    }

    public function setAverageModelBaseHours(?int $averageModelBaseHours): self
    {
        $this->averageModelBaseHours = $averageModelBaseHours;

        return $this;
    }

    public function getAverageIndustrialIncorporationParameter(): ?int
    {
        return $this->averageIndustrialIncorporationParameter;
    }

    public function setAverageIndustrialIncorporationParameter(?int $averageIndustrialIncorporationParameter): self
    {
        $this->averageIndustrialIncorporationParameter = $averageIndustrialIncorporationParameter;

        return $this;
    }

    public function getAverageOptionConfigurationParameterHours(): ?int
    {
        return $this->averageOptionConfigurationParameterHours;
    }

    public function setAverageOptionConfigurationParameterHours(?int $averageOptionConfigurationParameterHours): self
    {
        $this->averageOptionConfigurationParameterHours = $averageOptionConfigurationParameterHours;

        return $this;
    }

    public function getAverageUnitAllocatedHours(): ?int
    {
        return $this->averageUnitAllocatedHours;
    }

    public function setAverageUnitAllocatedHours(?int $averageUnitAllocatedHours): self
    {
        $this->averageUnitAllocatedHours = $averageUnitAllocatedHours;

        return $this;
    }

    public function getAverageActualHours(): ?int
    {
        return $this->averageActualHours;
    }

    public function setAverageActualHours(?int $averageActualHours): self
    {
        $this->averageActualHours = $averageActualHours;

        return $this;
    }

    public function getAverageFactoryStandardEfficiencyActual(): ?int
    {
        return $this->averageFactoryStandardEfficiencyActual;
    }

    public function setAverageFactoryStandardEfficiencyActual(?int $averageFactoryStandardEfficiencyActual): self
    {
        $this->averageFactoryStandardEfficiencyActual = $averageFactoryStandardEfficiencyActual;

        return $this;
    }
}

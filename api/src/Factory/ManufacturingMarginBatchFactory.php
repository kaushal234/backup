<?php

declare(strict_types=1);

namespace App\Factory;

use App\Dto\Finance\ManufacturingMarginBatch;
use App\Serializer\XlsxReader;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ManufacturingMarginBatchFactory
{
    /**
     * @var array
     */
    final public const PROPERTY_CONVERSION = [
        'er_id' => 'equipmentRecord',
        'cur' => 'currency',
    ];

    /**
     * @var array
     */
    final public const FLOAT_CONVERSION = [
        'ocp_hours' => 'optionConfigurationParameterHours',
        'act_hour' => 'actualHours',
        'std_hour' => 'standardHours',
        'std_lab_cost' => 'standardLabourCost',
        'act_lab_cost' => 'actualLabourCost',
        'std_mat' => 'standardMaterialCost',
        'act_mat' => 'actualMaterialCost',
        'std_other_mat' => 'standardOtherMaterialCost',
        'act_other_mat' => 'actualOtherMaterialCost',
        'std_other_dir_cost' => 'standardOtherDirectCost',
        'act_other_dir_cost' => 'actualOtherDirectCost',
        'factory_rev' => 'factoryRevenue',
    ];

    private readonly XlsxReader $xlsxReader;
    private readonly DenormalizerInterface $normalizer;

    public function __construct(XlsxReader $xlsxReader, DenormalizerInterface $normalizer)
    {
        $this->xlsxReader = $xlsxReader;
        $this->normalizer = $normalizer;
    }

    public function createManufacturingMarginBatch(File $file)
    {
        return $this->normalizer->denormalize(['margins' => $this->xlsxReader->normalize($file, ['propertyConversion' => self::PROPERTY_CONVERSION, 'floatConversion' => self::FLOAT_CONVERSION])], ManufacturingMarginBatch::class);
    }
}

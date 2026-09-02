<?php

declare(strict_types=1);

namespace Shared\Formatter\Manufacturing\JobShop;

use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;

class JobShopBillOfMaterialBenchmarkXlsFormatter
{
    private array $cachedUser = [];

    public function __construct()
    {
        global $kernel;

        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $this->serializer = new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer($classMetadataFactory, null, null, new PhpDocExtractor()),
            new GetSetMethodNormalizer()
        ]);
    }

    public function excel($jsbom, int $erp, array $availableLocations)
    {
        // Prepare header
        $xItems = [
            'partNumber' => 'Part Number',
            'level' => 'Level',
            'supplySource' => 'Supply Source',
            'itemDescription' => 'Description',
            'quantity' => 'Quantity',
            'price' => 'Purchase Price',
            'currency' => 'Purchase Currency',
            'buyer' => 'Buyer',
            'leadtTime' => 'Lead Time',
            'mainSupplier' => 'Main Supplier',
            'standardCost' => 'Std Cost',
            'costCurrency' => 'Std Cost Currency',
        ];

        unset($availableLocations[$erp]);
        foreach ($availableLocations as $locationsName) {
            $xItems['mainSupplier' . $locationsName] = 'Main Supplier' . ' ' . $locationsName;
            $xItems['price' . $locationsName] = 'Purchase Price' . ' ' . $locationsName;
            $xItems['standardCost' . $locationsName] = 'Std Cost' . ' ' . $locationsName;
            $xItems['standardCostMainCurrency' . $locationsName] = 'Std Cost Main Currency' . ' ' . $locationsName;
            $xItems['gap' . $locationsName] = 'Gap' . ' ' . $locationsName;
            $xItems['gapPercent' . $locationsName] = 'Gap %' . ' ' . $locationsName;
        }

        // Prepare data
        $jsbom = $this->serializer->normalize($jsbom);

        foreach ($jsbom as $key => $item) {
            $item['mainPurchasing']['buyer'] = $item['mainPurchasing']['buyer']['firstname'] . ' ' . $item['mainPurchasing']['buyer']['lastname'];
            $jsbom[$key] = [...$item, ...$item['mainPurchasing']];
            unset($item['mainPurchasing']);

            foreach ($item['purchasingBySites'] as $purchasingBySite) {
                foreach ($purchasingBySite as $name => $value) {
                    $jsbom[$key][$name.$availableLocations[$purchasingBySite['site']]] = 0.0 !== $purchasingBySite['price'] ? $value: '' ;

                }
            }
        }

        return new \tldXLS($jsbom, [
            "xItems" => $xItems,
            "showTitles" => true,
        ]);
    }
}
<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer;

use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials;
use App\ION\Serializer\Denormalizer\Manufacturing\JobShop\CustomizedBillOfMaterialsDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CustomizedBillOfMaterialsDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testDenormalizeWithEmptyData()
    {
        $customizedBillOfMaterialsDenormalizer = new CustomizedBillOfMaterialsDenormalizer();
        $result = $customizedBillOfMaterialsDenormalizer->denormalize([], 'type');
        $this->assertNull($result);
        $result = $customizedBillOfMaterialsDenormalizer->denormalize('', 'type');
        $this->assertNull($result);
        $result = $customizedBillOfMaterialsDenormalizer->denormalize(null, 'type');
        $this->assertNull($result);
        $result = $customizedBillOfMaterialsDenormalizer->denormalize(0, 'type');
        $this->assertNull($result);
    }

    public function testDataWithoutItem()
    {
        $context = [AbstractObjectNormalizer::GROUPS => []];
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);

        $denormalizerProphecy->denormalize([], CustomizedBillOfMaterials::class, null, $context + ['CUSTOMIZED_BILL_OF_MATERIAL_DENORMALIZER_ALREADY_CALLED' => true])->shouldNotBeCalled();

        $customizedBillOfMaterialsDenormalizer = new CustomizedBillOfMaterialsDenormalizer();
        $customizedBillOfMaterialsDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $customizedBillOfMaterialsDenormalizer->denormalize([], CustomizedBillOfMaterials::class, null, $context);
    }

    /**
     * @dataProvider customizedBillOfMaterialsProvider
     */
    public function testDenormalize(array $data, array $expected)
    {
        $context = [AbstractObjectNormalizer::GROUPS => []];
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);

        $denormalizerProphecy->denormalize($expected, CustomizedBillOfMaterials::class, null, $context + ['CUSTOMIZED_BILL_OF_MATERIAL_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn([]);

        $customizedBillOfMaterialsDenormalizer = new CustomizedBillOfMaterialsDenormalizer();
        $customizedBillOfMaterialsDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $customizedBillOfMaterialsDenormalizer->denormalize($data, CustomizedBillOfMaterials::class, null, $context);
    }

    public function customizedBillOfMaterialsProvider()
    {
        yield 'Without items key' => [
            [
                'project' => 'PO',
                'product' => 'OP',
                'billOfMaterials' => [
                    'code' => '',
                    'revision' => '',
                    'effectiveDate' => '',
                    'expiryDate' => '',
                ],
                'estimatedStandardCost' => '   ',
                'backflushIfMaterial' => '2',
                'supplyTime' => '   ',
                'phantom' => '2',
                'customized' => '1',
                'engineeringRevisionDrawing' => '',
            ],
            [
                'project' => 'PO',
                'product' => 'OP',
                'billOfMaterials' => [
                    'code' => '',
                    'revision' => '',
                    'effectiveDate' => '1969-12-31T19:00:00-05:00',
                    'expiryDate' => '1969-12-31T19:00:00-05:00',
                ],
                'estimatedStandardCost' => null,
                'backflushIfMaterial' => false,
                'supplyTime' => null,
                'phantom' => false,
                'customized' => true,
                'items' => [],
                'preventive' => false,
                'maintenance' => false,
                'overhaul' => false,
                'critical' => false,
                'engineeringRevisionDrawing' => null,
            ],
        ];

        yield 'With items key but no customizedBillOfMaterialItem key' => [
            [
                'project' => 'PO',
                'product' => 'OP',
                'billOfMaterials' => [
                    'code' => '',
                    'revision' => '',
                    'effectiveDate' => '',
                    'expiryDate' => '',
                ],
                'estimatedStandardCost' => '3.2',
                'backflushIfMaterial' => '2',
                'supplyTime' => '13',
                'phantom' => '2',
                'customized' => '0',
                'items' => [],
                'engineeringRevisionDrawing' => 'file.png     ',
            ],
            [
                'project' => 'PO',
                'product' => 'OP',
                'billOfMaterials' => [
                    'code' => '',
                    'revision' => '',
                    'effectiveDate' => '1969-12-31T19:00:00-05:00',
                    'expiryDate' => '1969-12-31T19:00:00-05:00',
                ],
                'estimatedStandardCost' => 3.2,
                'backflushIfMaterial' => false,
                'supplyTime' => 13,
                'phantom' => false,
                'customized' => false,
                'items' => [],
                'preventive' => false,
                'maintenance' => false,
                'overhaul' => false,
                'critical' => false,
                'engineeringRevisionDrawing' => 'file.png',
            ],
        ];
    }
}

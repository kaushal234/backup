<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer;

use App\ION\Serializer\Denormalizer\Manufacturing\MaterialDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MaterialDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testMaterialIsDenormalized()
    {
        $materialDenormalizer = new MaterialDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'position' => '123',
            'operation' => 'On en a gros',
            'item' => '&lt;un gros item',
            'itemDescription' => '&eacute;    oula le gros item',
            'itemOtherDescription' => 'je dirai meme plus oula le gros item',
            'warehouse' => 'dans la vallée ohoooh',
            'netQuantity' => 5.0,
            'estimatedQuantity' => 78.1,
            'actualQuantity' => 12.45,
            'unitOfMeasure' => 'en Pied',
            'revision' => 'coute ch&egrave;re ca?',
            'reportMaterial' => 'pas beau de cafter',
            'inventoryOnHand' => 4,
            'inventoryOnOrder' => 12,
            'costPrice' => 3.2,
            'currency' => 'rouble',
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'xml', ['MATERIAL_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $materialDenormalizer->setDenormalizer($denormalizerProphecy->reveal());

        $data = [
            'position' => '123',
            'operation' => 'On en a gros',
            'item' => '          <un gros item              ',
            'itemDescription' => '      é    oula le gros item              ',
            'itemOtherDescription' => ' je dirai meme plus oula le gros item ',
            'warehouse' => 'dans la vallée ohoooh',
            'netQuantity' => '5.0',
            'estimatedQuantity' => '78.1',
            'actualQuantity' => '12.45',
            'unitOfMeasure' => '             en Pied ',
            'revision' => 'coute chère ca?',
            'reportMaterial' => 'pas beau de cafter',
            'inventoryOnHand' => ' 4        ',
            'inventoryOnOrder' => '    12  ',
            'costPrice' => '      3.2  ',
            'currency' => 'rouble',
        ];
        $context = [];
        $denormalizedData = $materialDenormalizer->denormalize($data, 'type', 'xml', $context);
        self::assertSame($expectedData, $denormalizedData);
    }

    public function testMaterialIsDenormalizedWithNullcostPrice()
    {
        $materialDenormalizer = new MaterialDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'position' => '123',
            'opération' => 'On en a gros',
            'item' => 'un gros item',
            'itemDescription' => 'oula &lt;&gt;le &quot;gros&quot; item',
            'itemOtherDescription' => 'je dirai meme plus oula le gros item',
            'warehouse' => 'dans la vallée ohoooh',
            'netQuantity' => 5.0,
            'estimatedQuantity' => 78.1,
            'actualQuantity' => 12.45,
            'unitOfMeasure' => 'en Pied',
            'revision' => 'coute ch&egrave;re ca?',
            'reportMaterial' => 'pas beau de cafter',
            'inventoryOnHand' => 4,
            'inventoryOnOrder' => 12,
            'costPrice' => null,
            'currency' => 'rouble',
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'xml', ['MATERIAL_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $materialDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'position' => '123',
            'opération' => 'On en a gros',
            'item' => '          un gros item              ',
            'itemDescription' => '          oula <>le "gros" item              ',
            'itemOtherDescription' => ' je dirai meme plus oula le gros item ',
            'warehouse' => 'dans la vallée ohoooh',
            'netQuantity' => '5.0',
            'estimatedQuantity' => '78.1',
            'actualQuantity' => '12.45',
            'unitOfMeasure' => '             en Pied ',
            'revision' => 'coute chère ca?',
            'reportMaterial' => 'pas beau de cafter',
            'inventoryOnHand' => ' 4        ',
            'inventoryOnOrder' => '    12  ',
            'costPrice' => '          ',
            'currency' => 'rouble',
        ];
        $context = [];
        $denormalizedData = $materialDenormalizer->denormalize($data, 'type', 'xml', $context);
        self::assertSame($expectedData['costPrice'], $denormalizedData['costPrice']);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer;

use App\ION\Serializer\Denormalizer\Warehousing\TextItemLangDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @deprecated
 */
class TextItemLangDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testTextsItemAreDenormalized()
    {
        $textItemDenormalizer = new TextItemLangDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'lang' => '2',
            'name' => 'English',
            'texts' => [
                0 => 'Pas changé assiete pour fromage',
                1 => 'Pas changé assiete pour fromage 2',
            ],
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'xml', ['TEXT_ITEM_LANG_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $textItemDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'lang' => '2  ',
            'name' => '   English',
            'texts' => [
                'text' => [
                    0 => 'Pas changé assiete pour fromage',
                    1 => 'Pas changé assiete pour fromage 2',
                ],
            ],
        ];
        $context = [];

        $denormalizedData = $textItemDenormalizer->denormalize($data, 'type', 'xml', $context);
        self::assertSame($expectedData, $denormalizedData);
    }

    public function testOneTextsItemIsDenormalized()
    {
        $textItemDenormalizer = new TextItemLangDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'lang' => '2',
            'name' => 'English',
            'texts' => [
                0 => 'Pas changé assiete pour fromage',
            ],
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'xml', ['TEXT_ITEM_LANG_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $textItemDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'lang' => '2  ',
            'name' => '   English',
            'texts' => [
                'text' => 'Pas changé assiete pour fromage',
            ],
        ];
        $context = [];

        $denormalizedData = $textItemDenormalizer->denormalize($data, 'type', 'xml', $context);
        self::assertSame($expectedData, $denormalizedData);
    }
}

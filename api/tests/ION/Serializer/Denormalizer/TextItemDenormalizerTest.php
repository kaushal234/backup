<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer;

use App\ION\Serializer\Denormalizer\Warehousing\TextItemDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @deprecated
 */
class TextItemDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testTextItemIsDenormalized()
    {
        $textItemDenormalizer = new TextItemDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'code' => '123',
            'date' => '',
            'site' => '300',
            'textItemLangs' => [
                [
                    'lang' => '2',
                ],
            ],
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'xml', ['TEXT_ITEM_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $textItemDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'code' => '123  ',
            'date' => '',
            'site' => '  300',
            'textsByLanguage' => [
                'textByLanguage' => [
                    'lang' => '2',
                ],
            ],
        ];
        $context = [];
        $denormalizedData = $textItemDenormalizer->denormalize($data, 'type', 'xml', $context);
        self::assertSame($expectedData, $denormalizedData);
    }

    public function testTextItemEmptyCode()
    {
        $textItemDenormalizer = new TextItemDenormalizer();
        $data = [
            'code' => '',
            'date' => '',
            'site' => '  300',
        ];
        $context = [];
        $denormalizedData = $textItemDenormalizer->denormalize($data, 'type', 'xml', $context);
        self::assertNull($denormalizedData);
    }
}

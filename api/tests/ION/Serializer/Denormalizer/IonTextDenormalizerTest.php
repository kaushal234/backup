<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer;

use App\ION\Serializer\Denormalizer\IonTextDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class IonTextDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testIonTextIsDenormalized()
    {
        $textItemDenormalizer = new IonTextDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'code' => '123',
            'site' => '300',
            'textByLanguages' => [
                [
                    'lang' => '2',
                ],
            ],
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'xml', ['ION_TEXT_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $textItemDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'code' => '123  ',
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

    public function testIonTextWithEmptyCode()
    {
        $textItemDenormalizer = new IonTextDenormalizer();
        $data = [
            'code' => '',
            'site' => '  300',
        ];
        $context = [];
        $denormalizedData = $textItemDenormalizer->denormalize($data, 'type', 'xml', $context);
        self::assertNull($denormalizedData);
    }
}

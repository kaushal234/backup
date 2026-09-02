<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Decoder;

use App\Serializer\Decoder\MultipartDecoder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

class MultipartDecoderTest extends TestCase
{
    use ProphecyTrait;

    public function testNoRequest(): void
    {
        $requestProphecy = $this->prophesize(RequestStack::class);
        $requestProphecy->getCurrentRequest()->shouldBeCalledOnce()->willReturn(null);

        $propertyInfoExtractorProphecy = $this->prophesize(PropertyInfoExtractor::class);

        $decoder = new MultipartDecoder(
            $requestProphecy->reveal(),
            $propertyInfoExtractorProphecy->reveal(),
        );

        $result = $decoder->decode('', '');

        self::assertNull($result);
    }

    public function testDecode()
    {
        $request = new Request();
        $request->attributes->add([
            '_api_resource_class' => \stdClass::class,
        ]);
        $request->request->add([
            'boolean' => 'false',
            'string' => 'string',
        ]);

        $requestProphecy = $this->prophesize(RequestStack::class);
        $requestProphecy->getCurrentRequest()->shouldBeCalledOnce()->willReturn($request);

        $propertyInfoExtractorProphecy = $this->prophesize(PropertyInfoExtractor::class);
        $booleanType = new Type\BuiltinType(TypeIdentifier::BOOL);
        $propertyInfoExtractorProphecy->getType(\stdClass::class, 'boolean')->shouldBeCalledOnce()->willReturn($booleanType);
        $stringType = new Type\BuiltinType(TypeIdentifier::STRING);
        $propertyInfoExtractorProphecy->getType(\stdClass::class, 'string')->shouldBeCalledOnce()->willReturn($stringType);

        $decoder = new MultipartDecoder(
            $requestProphecy->reveal(),
            $propertyInfoExtractorProphecy->reveal(),
        );

        $result = $decoder->decode('', '');

        $expected = [
            'boolean' => false,
            'string' => 'string',
        ];
        self::assertSame($expected, $result);
    }
}

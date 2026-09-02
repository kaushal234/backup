<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Filter;

use ApiPlatform\OpenApi\Model\Parameter;
use App\Serializer\Filter\PropertyFilter;
use App\Tests\Mailer\DummyObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;

class PropertyFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testGetDescription()
    {
        $filter = new PropertyFilter(
            'yolo',
            false,
            ['foo', 'bar', 'buz' => ['biz' => ['zut', 'tuz' => 'wat'], 'fuz' => ['fez'], 'fur'], 'fiz' => ['faz']]
        );

        self::assertSame($filter->getDescription(DummyObject::class)['yolo'], ['type' => 'string', 'required' => false]);
        self::assertSame($filter->getDescription(DummyObject::class)['yolo[buz]'], ['type' => 'string', 'required' => false]);
        self::assertSame($filter->getDescription(DummyObject::class)['yolo[buz][biz]'], ['type' => 'string', 'required' => false]);
        self::assertSame($filter->getDescription(DummyObject::class)['yolo[buz][biz][tuz]'], ['type' => 'string', 'required' => false]);
        self::assertSame($filter->getDescription(DummyObject::class)['yolo[buz][fuz]'], ['type' => 'string', 'required' => false]);
        self::assertSame($filter->getDescription(DummyObject::class)['yolo[fiz]'], ['type' => 'string', 'required' => false]);
        self::assertSame($filter->getDescription(DummyObject::class)['yolo[]']['type'], 'string');
        self::assertSame($filter->getDescription(DummyObject::class)['yolo[]']['is_collection'], true);
        self::assertSame($filter->getDescription(DummyObject::class)['yolo[]']['required'], false);
        self::assertSame($filter->getDescription(DummyObject::class)['yolo[]']['description'], 'Allows you to reduce the response to contain only the properties you need. If your desired property is nested, you can address it using nested arrays. Example: yolo[]={propertyName}&yolo[]={anotherPropertyName}&yolo[{nestedPropertyParent}][]={nestedProperty}');

        /** @var Parameter $parameter */
        $parameter = $filter->getDescription(DummyObject::class)['yolo[]']['openapi'];
        self::assertInstanceOf(Parameter::class, $parameter);
        self::assertSame($parameter->getName(), 'yolo[]');
        self::assertSame($parameter->getRequired(), false);
        self::assertSame($parameter->getDescription(), 'Allows you to reduce the response to contain only the properties you need. If your desired property is nested, you can address it using nested arrays. Example: yolo[]={propertyName}&yolo[]={anotherPropertyName}&yolo[{nestedPropertyParent}][]={nestedProperty}');
        self::assertSame($parameter->getIn(), 'query');
        self::assertSame($parameter->getSchema(), [
            'type' => 'array',
            'items' => [
                'type' => 'string',
            ],
        ]);
    }

    public function testPropertiesAreUsedForCSVndCorrectlyFlattened()
    {
        $filter = new PropertyFilter();

        $context = ['attributes' => [], 'csv_headers_enabled' => true];

        $query = [
            'properties' => [
                'pouet' => ['camion'],
                'yeah',
                'ne' => ['st' => ['ed']],
            ],
        ];

        $filter->apply(new Request($query), true, [], $context);

        self::assertSame($context['csv_headers'], ['pouet.camion', 'yeah', 'ne.st.ed']);
        self::assertArrayNotHasKey('csv_headers_enabled', $context);
    }

    public function testPropertiesAreNotUsedForCSVByDefault()
    {
        $filter = new PropertyFilter();

        $context = ['attributes' => []];

        $query = [
            'properties' => [
                'pouet' => ['camion'],
                'yeah',
                'ne' => ['st' => ['ed']],
            ],
        ];

        $filter->apply(new Request($query), true, [], $context);

        self::assertArrayNotHasKey('csv_headers', $context);
    }
}

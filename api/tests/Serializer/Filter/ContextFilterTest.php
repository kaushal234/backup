<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Filter;

use App\Serializer\Filter\ContextFilter;
use App\Tests\Mailer\DummyObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ContextFilterTest extends TestCase
{
    public function testApply()
    {
        $request = new Request(['context' => ['datetime_format' => 'H:i']]);
        $context = ['base' => 'context'];

        $contextFilter = new ContextFilter();
        $contextFilter->apply($request, true, [], $context);

        self::assertSame(['datetime_format' => 'H:i', 'base' => 'context'], $context);
    }

    public function testApplyWillReplaceExistingContext()
    {
        $request = new Request(['context' => ['datetime_format' => 'H:i']]);
        $context = ['datetime_format' => 'Y-m-d'];

        $contextFilter = new ContextFilter();
        $contextFilter->apply($request, true, [], $context);

        self::assertSame(['datetime_format' => 'H:i'], $context);
    }

    public function testApplyWillNotOverrideGroupsNorPropertiesInContext()
    {
        $request = new Request(['context' => ['convertTo' => '/finance/currencies/2', 'groups' => ['evil'], 'attributes' => ['bad'], 'datetime_format' => 'H:m', 'something' => 'not_allowed']]);
        $context = [AbstractNormalizer::GROUPS => ['grp'], AbstractNormalizer::ATTRIBUTES => ['attr']];

        $contextFilter = new ContextFilter();
        $contextFilter->apply($request, true, [], $context);

        self::assertSame(['convertTo' => '/finance/currencies/2', 'datetime_format' => 'H:m', 'groups' => ['grp'], 'attributes' => ['attr']], $context);
    }

    public function testApplyWithoutContextInRequest()
    {
        $context = ['datetime_format' => 'H:m'];

        $contextFilter = new ContextFilter();
        $contextFilter->apply(new Request(), false, [], $context);

        self::assertSame(['datetime_format' => 'H:m'], $context);
    }

    public function testApplyWithGroupsInFilterAttribute()
    {
        $request = new Request(['context' => ['from' => 'query']], [], ['_api_filters' => ['context' => ['datetime_format' => 'H:m']]]);
        $context = ['some' => 'context'];

        $contextFilter = new ContextFilter();
        $contextFilter->apply($request, true, [], $context);

        self::assertSame(['datetime_format' => 'H:m', 'some' => 'context'], $context);
    }

    public function testApplyDoesntAccceptStrings()
    {
        $request = new Request(['context' => 'nope']);
        $context = ['some' => 'context'];

        $groupFilter = new ContextFilter();
        $groupFilter->apply($request, true, [], $context);

        self::assertSame(['some' => 'context'], $context);
    }

    public function testGetDescription()
    {
        $contextFilter = new ContextFilter('custom_context');
        $expectedDescription = [
            'custom_context[csv_delimiter]' => [
                'type' => 'string',
                'is_collection' => true,
                'required' => false,
            ],
            'custom_context[datetime_format]' => [
                'type' => 'string',
                'is_collection' => true,
                'required' => false,
            ],
            'custom_context[datetime_timezone]' => [
                'type' => 'string',
                'is_collection' => true,
                'required' => false,
            ],
            'custom_context[csv_headers_enabled]' => [
                'type' => 'string',
                'is_collection' => true,
                'required' => false,
            ],
            'custom_context[no_headers]' => [
                'type' => 'string',
                'is_collection' => true,
                'required' => false,
            ],
            'custom_context[convertTo]' => [
                'type' => 'string',
                'is_collection' => true,
                'required' => false,
            ],
        ];

        self::assertSame($expectedDescription, $contextFilter->getDescription(DummyObject::class));
    }
}

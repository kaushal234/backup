<?php

declare(strict_types=1);

namespace App\Tests\Jira\Mapping\Mapper;

use App\Jira\DataTransformer\ObjectToJiraId;
use App\Jira\DataTransformer\PropertyToCustomField;
use App\Jira\DataTransformer\PropertyToCustomUrlField;
use App\Jira\Mapping\Mapper\FieldMapper;
use App\Tests\Jira\Entity\JiraFieldAttributeDummy;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class FieldMapperTest extends TestCase
{
    use ProphecyTrait;

    public function testMappingIsCorrect()
    {
        $copyAnnotationDummy = new JiraFieldAttributeDummy('dummyPassion', 'dummyMoore');
        $class = new \ReflectionClass($copyAnnotationDummy);
        $mapper = new FieldMapper();

        $mapping = $mapper->getMapping($class);

        self::assertSame([
            'dummyPassion' => [[
                'transformer' => [ObjectToJiraId::class],
                'options' => ['field' => 'dumb'],
            ]],
            'dummyMoore' => [
                [
                    'transformer' => [PropertyToCustomField::class],
                    'options' => ['field' => 'custom'],
                ],
                [
                    'transformer' => [PropertyToCustomUrlField::class],
                    'options' => ['field' => 'custom', 'route' => 'route'],
                ],
            ],
        ], $mapping);
    }
}

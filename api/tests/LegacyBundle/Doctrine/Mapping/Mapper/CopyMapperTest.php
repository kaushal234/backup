<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Doctrine\Mapping\Mapper;

use LegacyBundle\Doctrine\Mapping\Mapper\CopyMapper;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\LegacyBundle\Entity\CopyAttributeDummy;
use Tests\LegacyBundle\Entity\CopyInheritedAttributeDummy;
use Tests\LegacyBundle\Entity\CopyInheritedTwoAttributesDummy;

class CopyMapperTest extends TestCase
{
    use ProphecyTrait;

    public function testMappingIsCorrect()
    {
        $copyAnnotationDummy = new CopyAttributeDummy('dummyPeche', 'dummyMoore');
        $class = new \ReflectionClass($copyAnnotationDummy);
        $copyMapper = new CopyMapper();

        $mapping = $copyMapper->getMapping($class);

        self::assertSame([
            'dummyPeche' => [
                'dummy_people' => [
                    'columns' => ['name'],
                    'options' => [],
                ],
            ],
            'dummyMoore' => [
                'dummy_people' => [
                    'columns' => ['email'],
                    'options' => [],
                ],
                'dummy_user' => [
                    'columns' => ['username'],
                    'options' => [],
                ],
            ],
        ], $mapping);
    }

    public function testMappingIsCorrectWhenInherited()
    {
        $copyInheritedAnnotationDummy = new CopyInheritedAttributeDummy('Peche', 'dummy@moore.com');
        $inheritedClass = new \ReflectionClass($copyInheritedAnnotationDummy);

        $copyMapper = new CopyMapper();

        $mapping = $copyMapper->getMapping($inheritedClass);

        self::assertSame([
            'dummyPeche' => [
                'dummy_people_inherited' => [
                    'columns' => ['name_inherited'],
                    'options' => [],
                ],
            ],
            'dummyMoore' => [
                'dummy_people' => [
                    'columns' => ['email'],
                    'options' => [],
                ],
                'dummy_user' => [
                    'columns' => ['username'],
                    'options' => [],
                ],
            ],
        ], $mapping);
    }

    public function testMappingIsCorrectWhenInheritedAndParentPropertyHasTwoAnnotation()
    {
        $copyInheritedAnnotationDummy = new CopyInheritedTwoAttributesDummy('Peche', 'dummy@moore.com');
        $inheritedClass = new \ReflectionClass($copyInheritedAnnotationDummy);

        $copyMapper = new CopyMapper();

        $mapping = $copyMapper->getMapping($inheritedClass);

        self::assertSame([
            'dummyPeche' => [
                'dummy_people_inherited' => [
                    'columns' => ['name_inherited'],
                    'options' => [],
                ],
            ],
            'dummyMoore' => [
                'dummy_people' => [
                    'columns' => ['email'],
                    'options' => [],
                ],
            ],
        ], $mapping);
    }
}

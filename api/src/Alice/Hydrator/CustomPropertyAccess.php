<?php

declare(strict_types=1);

namespace App\Alice\Hydrator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;

final class CustomPropertyAccess implements PropertyHydratorInterface
{
    use IsAServiceTrait;

    public function __construct(
        private readonly PropertyHydratorInterface $decoratedHydrator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function hydrate(ObjectInterface $object, Property $property, GenerationContext $context): ObjectInterface
    {
        $instance = $object->getInstance();
        $class = $instance::class;

        if ('id' === $property->getName() && !method_exists($class, 'setId')) {
            $metadata = $this->entityManager->getClassMetaData($class);
            $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new AssignedGenerator());

            try {
                $reflectionProperty = new \ReflectionProperty($class, 'id');
            } catch (\ReflectionException $exception) {
                // id property may be in parent class, so we check it
                $parentClass = (new \ReflectionClass($class))->getParentClass();
                $reflectionProperty = new \ReflectionProperty($parentClass->getName(), 'id');
            }
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($instance, $property->getValue());

            return new SimpleObject($object->getId(), $instance);
        }

        return $this->decoratedHydrator->hydrate($object, $property, $context);
    }
}

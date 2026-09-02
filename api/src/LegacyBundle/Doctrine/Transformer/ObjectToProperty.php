<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ObjectToProperty
{
    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function __invoke($object, array $options)
    {
        if (!isset($options['property'])) {
            throw new \Exception('You should set the "property" option to use the "ObjectToProperty" transformer.');
        }

        $nullValue = $options['nullValue'] ?? '';
        if (!\is_object($object)) {
            return $nullValue;
        }

        try {
            $value = $this->propertyAccessor->getValue($object, $options['property']);
        } catch (NoSuchPropertyException $noSuchPropertyException) {
            return 0;
        } catch (UnexpectedTypeException $unexpectedTypeException) {
            return $nullValue;
        }

        return null === $value ? $nullValue : $value;
    }
}

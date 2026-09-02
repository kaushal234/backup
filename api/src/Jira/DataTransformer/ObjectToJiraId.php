<?php

declare(strict_types=1);

namespace App\Jira\DataTransformer;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ObjectToJiraId implements JiraFieldDataTransformerInterface
{
    public function __construct(
        private readonly PropertyAccessorInterface $propertyAccessor
    ) {
    }

    public function __invoke($object, array $options)
    {
        try {
            $value = $this->propertyAccessor->getValue($object, 'id');
        } catch (NoSuchPropertyException $noSuchPropertyException) {
            return 0;
        } catch (UnexpectedTypeException $unexpectedTypeException) {
            return [];
        }

        return null === $value ? [] : [$options['field'] => ['id' => $value]];
    }
}

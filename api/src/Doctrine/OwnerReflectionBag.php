<?php

declare(strict_types=1);

namespace App\Doctrine;

class OwnerReflectionBag
{
    private \ReflectionClass $reflectionClass;

    private \ReflectionProperty $reflectionProperty;

    /**
     * RelationMetadataBag constructor.
     */
    public function __construct(\ReflectionClass $sourceEntity, \ReflectionProperty $reflectionProperty)
    {
        $this->reflectionClass = $sourceEntity;
        $this->reflectionProperty = $reflectionProperty;
    }

    public function getReflectionClass(): \ReflectionClass
    {
        return $this->reflectionClass;
    }

    /**
     * @return $this
     */
    public function setReflectionClass(\ReflectionClass $reflectionClass)
    {
        $this->reflectionClass = $reflectionClass;

        return $this;
    }

    public function getReflectionProperty(): \ReflectionProperty
    {
        return $this->reflectionProperty;
    }

    /**
     * @return $this
     */
    public function setReflectionProperty(\ReflectionProperty $reflectionProperty)
    {
        $this->reflectionProperty = $reflectionProperty;

        return $this;
    }
}

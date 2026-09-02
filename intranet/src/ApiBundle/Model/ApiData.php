<?php

declare(strict_types=1);

namespace ApiBundle\Model;

use ApiBundle\Iri\Iri;

class ApiData implements \ArrayAccess, \IteratorAggregate
{
    public function __construct(private array $data = [])
    {
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        if (\array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
    }

    public function __isset($name): bool
    {
        return \array_key_exists($name, $this->data);
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function getIri(): string
    {
        return $this->data['@id'];
    }

    public function getIriId(): int
    {
        return (int) Iri::id($this->data);
    }

    public function getIriType(): ?string
    {
        return Iri::type($this->data);
    }

    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}

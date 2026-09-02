<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Model;

use ArrayIterator;

use function array_key_exists;

abstract class AbstractApiData implements ApiDataInterface
{
    /**
     * @var array<string|mixed>
     */
    private $data;

    /**
     * @param array<string|mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed|null
     */
    public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    abstract public function getIri(): string;

    abstract public function getIriId(): int;

    abstract public function getIriType(): ?string;

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset): mixed
    {
        return array_key_exists($offset, $this->data) ? $this->data[$offset] : null;
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

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @return array<string|mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}

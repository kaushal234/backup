<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Hydra;

use Alvest\TwigHelper\Model\ApiData;
use Alvest\TwigHelper\Model\ApiDataInterface;
use ArrayIterator;
use InvalidArgumentException;
use RangeException;

use function array_key_exists;
use function count;
use function sprintf;

class HydraCollection implements HydraCollectionInterface
{
    /**
     * @var array<ApiDataInterface>|ApiDataInterface
     */
    private $members = [];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $data)
    {
        foreach ($data['hydra:member'] as $member) {
            $this->members[] = new ApiData($member);
        }
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->members);
    }

    public function offsetGet($offset): mixed
    {
        if (!array_key_exists($offset, $this->members)) {
            throw new RangeException(sprintf('The offset "%s" does not exists', $offset));
        }

        return $this->members[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new InvalidArgumentException('Collection is read only');
    }

    public function offsetUnset($offset): void
    {
        throw new InvalidArgumentException('Collection is read only');
    }

    public function count(): int
    {
        return count($this->members);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->members);
    }

    /**
     * {@inheritDoc}
     */
    public function getSimpleArrayCopy(): array
    {
        $data = [];
        foreach ($this->members as $member) {
            $data[] = $member->toArray();
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexedCollection(string $key): array
    {
        $data = [];
        foreach ($this->members as $member) {
            if (!isset($member[$key])) {
                throw new RangeException(sprintf('The key "%s" does not exists', $key));
            }

            $data[$member[$key]] = $member->toArray();
        }

        return $data;
    }

    public function first(): ?ApiDataInterface
    {
        return $this[0] ?? null;
    }

    public function last(): ?ApiDataInterface
    {
        return $this[$this->count() - 1] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->members;
    }
}

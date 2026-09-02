<?php

declare(strict_types=1);

namespace ApiBundle\Hydra;

use ApiBundle\Model\ApiData;

/**
 * Class HydraCollection.
 */
class HydraCollection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @var HydraPagination
     */
    public $pagination;

    /**
     * @var HydraMetadataBag
     */
    public $metadata;
    /**
     * @var array|ApiData[]
     */
    private array $members = [];

    public function __construct(array $data)
    {
        foreach ($data['hydra:member'] as $member) {
            $this->members[] = new ApiData($member);
        }
        $this->pagination = new HydraPagination($data);
        $this->metadata = new HydraMetadataBag($data);
    }

    /**
     * {@inheritdoc}
     *
     * @return ApiData[]|\ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->members);
    }

    public function getSimpleArrayCopy(): array
    {
        $data = [];
        foreach ($this->members as $member) {
            $data[] = $member->toArray();
        }

        return $data;
    }

    public function getIndexedCollection(string $key): array
    {
        $data = [];
        foreach ($this->members as $member) {
            if (!isset($member[$key])) {
                throw new \RangeException(\sprintf('The key "%s" does not exists', $key));
            }

            $data[$member[$key]] = $member->toArray();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->members);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): ApiData
    {
        if (!\array_key_exists($offset, $this->members)) {
            throw new \RangeException(\sprintf('The offset "%s" does not exists', $offset));
        }

        return $this->members[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        throw new \InvalidArgumentException('Collection is read only');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        throw new \InvalidArgumentException('Collection is read only');
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->members);
    }

    public function first(): ?ApiData
    {
        return $this[0] ?? null;
    }

    public function last(): ?ApiData
    {
        return $this[$this->count() - 1] ?? null;
    }

    /**
     * Return all elements as an array.
     *
     * @return ApiData[]
     */
    public function all(): array
    {
        return $this->members;
    }
}

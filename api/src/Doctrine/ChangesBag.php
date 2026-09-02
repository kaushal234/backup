<?php

declare(strict_types=1);

namespace App\Doctrine;

class ChangesBag implements \Countable
{
    private readonly \SplStack $changes;

    /**
     * ChangesCollection constructor.
     */
    public function __construct()
    {
        $this->changes = new \SplStack();
    }

    public function push(Change $change)
    {
        $this->changes->push($change);
    }

    /**
     * @return Change
     */
    public function pop()
    {
        return $this->changes->pop();
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->changes);
    }
}

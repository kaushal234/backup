<?php

declare(strict_types=1);

namespace App\DeletionVoter\Reason;

class RejectedDeletionDetailedReason extends RejectedDeletionReason
{
    /**
     * @var string
     */
    final public const MESSAGE = "The %type% '%label%' is not deletable because it is used by %count% %countedType% (%identifiers%)";

    protected string $identifiers;

    protected $count;

    protected $countedType;

    public function setCount($count): self
    {
        $this->count = $count;

        return $this;
    }

    public function setCountedType($countedType): self
    {
        $this->countedType = $countedType;

        return $this;
    }

    public function setIdentifiers(array $identifiers): self
    {
        $this->identifiers = implode(', ', $identifiers);
        $this->setCount(\count($identifiers));

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\DeletionVoter\Reason;

class RejectedDeletionReason implements \Stringable
{
    /**
     * @var string
     */
    public const MESSAGE = "The %type% '%label%' is not deletable";

    protected string $type;

    protected string $label;

    final public function __toString(): string
    {
        $replacements = $this->getReplacements();

        return str_replace(array_keys($replacements), array_values($replacements), static::MESSAGE);
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    protected function getReplacements(): array
    {
        $replacements = [];
        foreach (get_object_vars($this) as $name => $value) {
            $replacements['%'.$name.'%'] = $value;
        }

        return $replacements;
    }
}

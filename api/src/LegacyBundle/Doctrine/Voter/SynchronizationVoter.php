<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Voter;

class SynchronizationVoter
{
    private bool $enabled;

    public function __construct($enabled = true)
    {
        $this->enabled = $enabled;
    }

    public function enable()
    {
        $this->enabled = true;
    }

    public function disable()
    {
        $this->enabled = false;
    }

    public function vote()
    {
        return $this->enabled;
    }
}

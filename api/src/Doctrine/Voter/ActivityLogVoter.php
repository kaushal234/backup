<?php

declare(strict_types=1);

namespace App\Doctrine\Voter;

class ActivityLogVoter
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

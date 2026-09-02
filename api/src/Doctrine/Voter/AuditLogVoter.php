<?php

declare(strict_types=1);

namespace App\Doctrine\Voter;

class AuditLogVoter
{
    public function __construct(private bool $enabled = true)
    {
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

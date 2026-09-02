<?php

declare(strict_types=1);

namespace App\Dto;

class StatusInput
{
    public string $status;
    public ?string $comment = null;
}

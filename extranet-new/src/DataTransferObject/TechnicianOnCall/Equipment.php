<?php

declare(strict_types=1);

namespace App\DataTransferObject\TechnicianOnCall;

use Symfony\Component\Validator\Constraints as Assert;

class Equipment
{
    #[Assert\NotBlank(message: 'extranet.error.equipment')]
    public ?string $iri = null;
}

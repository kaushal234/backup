<?php

declare(strict_types=1);

namespace App\DataTransferObject\TechnicianOnCall;

use Symfony\Component\Validator\Constraints as Assert;

class Airport
{
    #[Assert\NotBlank(message: 'extranet.error.airport')]
    public ?string $iri = null;
}

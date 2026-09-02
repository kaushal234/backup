<?php

declare(strict_types=1);

namespace App\DataTransferObject;

use Symfony\Component\Validator\Constraints as Assert;

class Country
{
    #[Assert\NotBlank(message: 'extranet.error.country')]
    public ?string $iri = null;
}

<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class FileInput
{
    #[Assert\NotBlank]
    #[Assert\NotNull]
    public int $width;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    public int $height;
}

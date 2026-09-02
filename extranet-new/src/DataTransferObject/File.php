<?php

declare(strict_types=1);

namespace App\DataTransferObject;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class File
{
    #[Assert\NotBlank(message: 'extranet.error.file')]
    public UploadedFile $file;
    public ?string $description = null;
}

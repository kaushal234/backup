<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile as HttpUploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UploadedFile
{
    public function __construct(
        #[Assert\NotNull(message: 'No file uploaded under "file" field')]
        #[Assert\File(mimeTypes: ['application/pdf'], mimeTypesMessage: 'Unsupported file type. Allowed: {{ types }}')]
        public ?HttpUploadedFile $file = null,
    ) {
    }
}

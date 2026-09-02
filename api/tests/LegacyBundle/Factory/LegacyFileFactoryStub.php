<?php

declare(strict_types=1);

namespace App\Tests\LegacyBundle\Factory;

use LegacyBundle\Entity\LegacyFile;
use LegacyBundle\Factory\LegacyFileFactory;
use Symfony\Component\HttpFoundation\File\File;

class LegacyFileFactoryStub extends LegacyFileFactory
{
    public function createLegacyFile(LegacyFile $file): File
    {
        return new File($this->parameters->get('legacy.upload_dir').'/../../../tests/fixtures/'.$file->filePath);
    }

    public function createLegacyFileFromPath(string $path): File
    {
        return new File($this->parameters->get('legacy.upload_dir').'/../../../tests/fixtures/'.$path);
    }
}

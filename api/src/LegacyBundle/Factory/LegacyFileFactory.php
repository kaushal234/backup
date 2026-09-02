<?php

declare(strict_types=1);

namespace LegacyBundle\Factory;

use LegacyBundle\Entity\LegacyFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

class LegacyFileFactory
{
    protected ParameterBagInterface $parameters;

    public function __construct(ParameterBagInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    public function createLegacyFile(LegacyFile $legacyFile): File
    {
        return new File($this->parameters->get('legacy.upload_dir').'/'.$legacyFile->filePath);
    }

    public function createLegacyFileFromPath(string $path): File
    {
        return new File($this->parameters->get('legacy.upload_dir').'/'.$path);
    }
}

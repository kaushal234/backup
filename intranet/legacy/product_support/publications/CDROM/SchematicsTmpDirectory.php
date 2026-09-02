<?php

declare(strict_types=1);

use Symfony\Component\Filesystem\Filesystem;

class SchematicsTmpDirectory
{
    public $path = '';
    public $fileSystem;

    public function __construct(string $subPath)
    {
        $this->fileSystem = new Filesystem();

        global $HOME_DIR;
        $this->path = sprintf('%s/cache/manuals/%s', $HOME_DIR, $subPath);
        $this->mkdir();
    }

    public function mkdir(): void
    {
        if ($this->fileSystem->exists($this->path)) {
            return;
        }
        $this->fileSystem->mkdir($this->path);
    }

    public function remove()
    {
        if (!$this->fileSystem->exists($this->path)) {
            return;
        }
        $this->fileSystem->remove($this->path);
    }
}
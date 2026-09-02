<?php

declare(strict_types=1);

namespace App\Formatter\Snappy;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class FileFormatter
{
    private readonly FormatterInterface $formatter;

    private readonly Filesystem $filesystem;

    private readonly ParameterBagInterface $parameters;

    /**
     * FileFormatter constructor.
     */
    public function __construct(FormatterInterface $formatter, Filesystem $filesystem, ParameterBagInterface $parameters)
    {
        $this->formatter = $formatter;
        $this->filesystem = $filesystem;
        $this->parameters = $parameters;
    }

    public function convert($object, string $purpose, string $filename, string $format, array $extraContext = []): \SplFileObject
    {
        $output = $this->formatter->convert($object, $purpose, $format, $extraContext);

        $path = $this->getPath($filename);

        $this->filesystem->dumpFile($path, $output);

        return new \SplFileObject($path);
    }

    public function clean(string $filename): void
    {
        $this->filesystem->remove([$this->getPath($filename)]);
    }

    private function getPath(string $filename): string
    {
        return $this->parameters->get('legacy.upload_dir').\DIRECTORY_SEPARATOR.$filename;
    }
}

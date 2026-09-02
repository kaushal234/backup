<?php

declare(strict_types=1);

namespace App\Formatter\Snappy\Converter;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Converter that uses ghostscript command to change PDF version.
 */
class PdfConverter
{
    protected Filesystem $fileSystem;

    protected string $tmp;

    protected string $baseCommand = 'gs -sDEVICE=pdfwrite -dCompatibilityLevel=%s -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -dColorConversionStrategy=/LeaveColorUnchanged -dEncodeColorImages=false -dEncodeGrayImages=false -dEncodeMonoImages=false -dDownsampleMonoImages=false -dDownsampleGrayImages=false -dDownsampleColorImages=false -dAutoFilterColorImages=false -dAutoFilterGrayImages=false -dColorImageFilter=/FlateEncode -dGrayImageFilter=/FlateEncode -sOutputFile=%s %s';

    public function __construct(Filesystem $fileSystem, ?string $tmp = null)
    {
        $this->fileSystem = $fileSystem;
        $this->tmp = $tmp ?: sys_get_temp_dir();
    }

    public function convert(string $file, string $newVersion): void
    {
        $tmpFile = $this->fileSystem->tempnam('/tmp', 'pdf_version_changer_', '.pdf');
        if (!$this->fileSystem->exists($tmpFile)) {
            throw new \RuntimeException("The generated file '{$tmpFile}' was not found.");
        }

        try {
            $this->run($file, $tmpFile, $newVersion);
            $this->fileSystem->copy($tmpFile, $file, true);
        } finally {
            $this->fileSystem->remove($tmpFile);
        }
    }

    public function run(string $originalFile, string $newFile, string $newVersion): void
    {
        $command = explode(' ', \sprintf($this->baseCommand, $newVersion, $newFile, $originalFile));

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public function guess(string $file): string
    {
        $version = $this->guessVersion($file);

        if (null === $version) {
            throw new \RuntimeException("Can't guess version. The file '{$file}' is a PDF file?");
        }

        return $version;
    }

    protected function guessVersion(string $filename): ?string
    {
        $fp = @fopen($filename, 'r');

        if (!$fp) {
            return null;
        }

        /* Reset file pointer to the start */
        fseek($fp, 0);

        /* Read 1024 bytes from the start of the PDF */
        preg_match('/%PDF-(\d\.\d)/', fread($fp, 1024), $match);

        fclose($fp);

        if (isset($match[1])) {
            return $match[1];
        }

        return null;
    }
}

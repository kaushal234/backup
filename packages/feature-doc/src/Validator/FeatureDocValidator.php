<?php

declare(strict_types=1);

namespace Alvest\FeatureDoc\Validator;

use Alvest\FeatureDoc\Scanner\FeatureDocScanner;

final class FeatureDocValidator
{
    public function __construct(
        private readonly FeatureDocScanner $scanner = new FeatureDocScanner(),
    ) {
    }

    /**
     * @param resource $stdout
     * @param resource $stderr
     */
    public function run(?string $projectDir, $stdout, $stderr): int
    {
        if (null === $projectDir) {
            $this->writeln($stderr, 'Usage: feature-doc-validate <projectDir>');
            $this->writeln($stderr, 'Example: feature-doc-validate api');

            return 2;
        }

        $projectDir = \rtrim($projectDir, \DIRECTORY_SEPARATOR);

        if (!\is_dir($projectDir)) {
            $candidate = \getcwd().\DIRECTORY_SEPARATOR.$projectDir;
            if (\is_dir($candidate)) {
                $projectDir = $candidate;
            }
        }

        if (!\is_dir($projectDir)) {
            $this->writeln($stderr, "Error: projectDir not found: {$projectDir}");

            return 2;
        }

        $srcDir = $projectDir.\DIRECTORY_SEPARATOR.'src';
        $docRoot = $projectDir.\DIRECTORY_SEPARATOR.'documentation';

        if (!\is_dir($srcDir)) {
            $this->writeln($stderr, "Error: src directory not found: {$srcDir}");

            return 2;
        }

        if (!\is_dir($docRoot)) {
            $this->writeln($stderr, "Error: documentation directory not found: {$docRoot}");

            return 2;
        }

        $hits = $this->scanner->scan($srcDir);
        $missing = [];
        $unsafe = [];

        foreach ($hits as $hit) {
            $path = \ltrim($hit['path'], '/\\');

            if (\str_contains($path, '..')) {
                $unsafe[] = $hit + ['reason' => 'Forbidden path (..).'];
                continue;
            }

            $full = $docRoot.\DIRECTORY_SEPARATOR.$path;

            if (!\is_file($full)) {
                $missing[] = $hit + ['full' => $full];
            }
        }

        if (!$missing && !$unsafe) {
            $this->writeln($stdout, 'OK — no missing documentation files. ('.\count($hits).' FeatureDoc attribute(s) found)');

            return 0;
        }

        if ($unsafe) {
            $this->writeln($stderr, 'Rejected paths:');
            foreach ($unsafe as $u) {
                $this->writeln($stderr, " - {$u['file']}:{$u['line']} -> {$u['path']} ({$u['reason']})");
            }
        }

        if ($missing) {
            $this->writeln($stderr, 'Missing documentation files:');
            foreach ($missing as $m) {
                $rel = 'documentation/'.\ltrim($m['path'], '/\\');
                $this->writeln($stderr, " - {$m['file']}:{$m['line']} -> {$rel} (expected at: {$m['full']})");
            }
        }

        return 1;
    }

    /**
     * @param resource $stream
     */
    private function writeln($stream, string $msg): void
    {
        \fwrite($stream, $msg.\PHP_EOL);
    }
}

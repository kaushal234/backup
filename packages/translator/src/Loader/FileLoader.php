<?php

declare(strict_types=1);

namespace Alvest\Translator\Loader;

use function in_array;

use const DIRECTORY_SEPARATOR;

class FileLoader
{
    public const TRANSLATIONS_DIR = __DIR__.'/../../translations';
    public const FORMAT = ['po', 'yaml'];

    /**
     * @param array<string> $results
     *
     * @return array<string>
     */
    public static function load(string $dir = self::TRANSLATIONS_DIR, array &$results = []): array
    {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            $pathinfo = pathinfo($path);

            if (is_dir($path) && '.' !== $value && '..' !== $value) {
                self::load($path, $results);
            } elseif (in_array($pathinfo['extension'] ?? null, self::FORMAT, true)) {
                $results[] = $path;
            }
        }

        return $results;
    }
}

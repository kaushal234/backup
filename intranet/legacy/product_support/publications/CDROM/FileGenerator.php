<?php
declare(strict_types=1);

class FileGenerator
{
    public function generateFromContent(string $content, string $path): string
    {
        file_put_contents($path, $content);

        // Find extension and rename the file
        $extension = explode('/', mime_content_type($path))[1];
        $pathWithExtension = $path.'.'.$extension;
        rename($path, $pathWithExtension);

        return $pathWithExtension;
    }
}
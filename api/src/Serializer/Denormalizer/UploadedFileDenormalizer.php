<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Passthrough denormalizer: when the multipart decoder injects a real
 * UploadedFile under a DTO property typed as UploadedFile, the default
 * ObjectNormalizer attempts to reconstruct it and fails because its
 * constructor requires $path/$originalName. We just return the object
 * as-is.
 */
final class UploadedFileDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): UploadedFile
    {
        return $data;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UploadedFile && is_a($type, UploadedFile::class, true);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }
}

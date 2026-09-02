<?php

declare(strict_types=1);

namespace AppBundle\Manager;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class FileManager
{
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * Manage the upload or the deletion of an image from the data of an app_image form.
     *
     * @param ApiData|array $object
     */
    public function updateImage($object, $resource, array $image, $operation, $fileId, bool $public = false): void
    {
        if (isset($image['delete']) && $image['delete']) {
            $this->deleteFile($object, $resource, \sprintf('%s/%s', $operation, $fileId));
        }

        if (isset($image['file']) && $image['file'] instanceof UploadedFile) {
            $this->uploadFile($object, $image['file'], $resource, null, $operation, $public);
        }
    }

    /**
     * @param array|ApiData $object
     * @param string        $resource
     * @param string        $operation
     */
    public function deleteFile($object, $resource, $operation): void
    {
        $objectId = Iri::id($object['@id']);

        $this->client->request($resource, $objectId, $operation, 'DELETE');

        unset($object['file']);
    }

    /**
     * @param array|ApiData $object
     * @param string        $resource
     */
    public function uploadFile(
        $object,
        UploadedFile $file,
        $resource,
        $description = null,
        $operation = 'files',
        $preserveFilename = false,
        bool $public = false,
        ?int $width = null,
        ?int $height = null,
    ): void {
        $objectId = Iri::id($object['@id']);

        $filename = null;
        if (true === $preserveFilename) {
            $filename = $file->getClientOriginalName();
        }
        $multiPart = ['file' => DataPart::fromPath($file->getPathname(), $filename)];
        $metadataKeys = [
            'description' => $description,
            'width' => $width,
            'height' => $height,
        ];

        foreach ($metadataKeys as $key => $value) {
            if (null !== $value) {
                $multiPart[$key] = (string) $value;
            }
        }

        $multiPart['public'] = $public ? '1' : '0';

        $formData = new FormDataPart($multiPart);

        $this->client->request($resource, $objectId, $operation, 'POST', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);
    }

    public function getImageDimensions(?string $imageSize): array
    {
        $dimensions = [
            'large' => ['width' => 600, 'height' => 600],
            'medium' => ['width' => 300, 'height' => 300],
            'small' => ['width' => 150, 'height' => 150],
            'large_horizontal' => ['width' => 900, 'height' => 600],
            'medium_horizontal' => ['width' => 600, 'height' => 300],
            'small_horizontal' => ['width' => 300, 'height' => 150],
            'large_vertical' => ['width' => 600, 'height' => 900],
            'medium_vertical' => ['width' => 300, 'height' => 600],
            'small_vertical' => ['width' => 150, 'height' => 300],
        ];

        return $dimensions[$imageSize] ?? ['width' => null, 'height' => null];
    }
}

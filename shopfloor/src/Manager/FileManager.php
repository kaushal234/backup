<?php

declare(strict_types=1);

namespace App\Manager;

use App\Client\ApiClient;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class FileManager
{
    private ApiClient $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public function uploadFile(array $data, $resource, $operation = 'files', $description = null, $preserveFilename = false)
    {
        $file = new \basicFile($data['tmp_name']);
        $formFields = ['file' => DataPart::fromPath($file->getFilePath())];

        if (true === $preserveFilename) {
            $formFields['filename'] = $data['name'];
        }

        if (null !== $description) {
            $formFields['description'] = $description;
        }

        $formData = new FormDataPart($formFields);
        $this->client->request(ApiClient::METHOD_POST, \sprintf('%s/%s', $resource['@id'], $operation), [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);
    }

    public function dowloadFile(string $requestUrl, $inline = false, $filename = 'file')
    {
        $file = $this->client->request(ApiClient::METHOD_GET, $requestUrl);

        $headers = $file->getHeaders();
        $contentType = $headers['content-type'][0];
        $contentDisposition = $inline ? 'inline' : 'attachment';

        header("Content-Type: $contentType");
        header("Content-Disposition: $contentDisposition; filename=\"$filename\"");
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        return $file->getContent();
    }
}

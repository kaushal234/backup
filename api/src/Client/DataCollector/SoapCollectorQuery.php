<?php

declare(strict_types=1);

namespace App\Client\DataCollector;

use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Cloner\Data;

class SoapCollectorQuery
{
    private const DEFAULT_DATA = [
        'requestBody' => [],
        'operation' => '',
        'xmlRequest' => '',
        'requestHeaders' => '',
        'xmlResponse' => '',
        'responseHeaders' => '',
        'executionTime' => '',
    ];

    protected array $data = self::DEFAULT_DATA;

    private readonly ClonerInterface $cloner;

    public function __construct(ClonerInterface $cloner)
    {
        $this->cloner = $cloner;
    }

    public function getRequestBody(): Data
    {
        return $this->cloner->cloneVar($this->data['requestBody']);
    }

    public function setRequestBody(?array $requestBody): self
    {
        $this->data['requestBody'] = $requestBody ?? [];

        return $this;
    }

    public function getOperation(): string
    {
        return $this->data['operation'];
    }

    public function setOperation(?string $operation): self
    {
        $this->data['operation'] = $operation ?? '';

        return $this;
    }

    public function getXMLRequest(): string
    {
        return $this->data['xmlRequest'];
    }

    public function setXMLRequest(?string $xmlRequest): self
    {
        if (null === $xmlRequest) {
            $this->data['xmlRequest'] = '';

            return $this;
        }
        $document = new \DOMDocument();
        $document->loadXML($xmlRequest);
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        $this->data['xmlRequest'] = $document->saveXML();

        return $this;
    }

    public function getXMLResponse(): string
    {
        return $this->data['xmlResponse'];
    }

    public function setXMLResponse(?string $xmlResponse): self
    {
        if ('' === (string) $xmlResponse) {
            $this->data['xmlResponse'] = '';

            return $this;
        }
        $document = new \DOMDocument();
        $document->loadXML($xmlResponse);
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        $this->data['xmlResponse'] = $document->saveXML();

        return $this;
    }

    public function getRequestHeaders(): string
    {
        return $this->data['requestHeaders'];
    }

    public function setRequestHeaders(?string $requestHeaders): self
    {
        $this->data['requestHeaders'] = $requestHeaders ?? '';

        return $this;
    }

    public function getResponseHeaders(): string
    {
        return $this->data['responseHeaders'];
    }

    public function setResponseHeaders(?string $responseHeaders): self
    {
        $this->data['responseHeaders'] = $responseHeaders ?? '';

        return $this;
    }

    public function getExecutionTime(): string
    {
        return $this->data['executionTime'];
    }

    public function setExecutionTime(string $executionTime): self
    {
        $this->data['executionTime'] = \sprintf('%s ms', $executionTime);

        return $this;
    }
}

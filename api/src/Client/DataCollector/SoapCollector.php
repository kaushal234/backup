<?php

declare(strict_types=1);

namespace App\Client\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class SoapCollector extends DataCollector
{
    public function addQuery(SoapCollectorQuery $query): self
    {
        $this->data[] = $query;

        return $this;
    }

    public function getQueries(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'app.soap_collector';
    }

    public function reset(): void
    {
        $this->data = [];
    }
}

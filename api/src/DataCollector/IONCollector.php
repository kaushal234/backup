<?php

declare(strict_types=1);

namespace App\DataCollector;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IONCollector extends AbstractDataCollector
{
    public function addQuery(IONCollectorQuery $query): self
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
        return 'app.ion_collector';
    }

    public function reset(): void
    {
        $this->data = [];
    }
}

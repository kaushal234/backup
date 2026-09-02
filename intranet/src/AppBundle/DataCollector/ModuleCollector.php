<?php

declare(strict_types=1);

namespace AppBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ModuleCollector extends DataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->data = [
            'module' => $request->attributes->get('alvest_module'),
        ];
    }

    public function reset(): void
    {
        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'tld.module_collector';
    }

    public function getModule()
    {
        return $this->data['module'] ?? null;
    }
}

<?php

declare(strict_types=1);

namespace App\Jira\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface JiraClientInterface
{
    public function doRequest(string $operation, ?string $id = null, array $options = [], string $method = Request::METHOD_GET): Response;
}

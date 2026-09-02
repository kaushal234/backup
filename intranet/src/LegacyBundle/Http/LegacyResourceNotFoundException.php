<?php

declare(strict_types=1);

namespace LegacyBundle\Http;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LegacyResourceNotFoundException extends HttpException
{
    public function __construct($statusCode = Response::HTTP_NOT_FOUND, $message = 'Legacy resource not found', ?\Exception $previous = null, array $headers = [], $code = 0)
    {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}

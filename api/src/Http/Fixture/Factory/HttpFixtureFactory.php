<?php

declare(strict_types=1);

namespace App\Http\Fixture\Factory;

use App\Http\Fixture\Resource\HttpFixture;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpFixtureFactory
{
    public function createFromResponse(ResponseInterface $response): HttpFixture
    {
        $fixture = new HttpFixture();
        $fixture->headers = $response->getHeaders();
        $fixture->statusCode = $response->getStatusCode();
        $fixture->content = $response->getContent(false);

        return $fixture;
    }

    public function createFromJSON(string $content): HttpFixture
    {
        $data = json_decode($content, true);

        $fixture = new HttpFixture();
        $fixture->statusCode = $data['statusCode'];
        $fixture->headers = $data['headers'];
        $fixture->content = $data['content'];

        return $fixture;
    }
}

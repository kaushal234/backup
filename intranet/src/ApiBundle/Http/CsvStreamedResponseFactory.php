<?php

declare(strict_types=1);

namespace ApiBundle\Http;

use ApiBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvStreamedResponseFactory
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function create($resource, array $parameters, $filename = 'data.csv')
    {
        $mimeType = 'text/csv';

        $itemsPerPage = $parameters['itemsPerPage'] ?? 500;

        $parameters['itemsPerPage'] = 0;

        $results = $this->client->findBy($resource, $parameters);

        $pageNumber = (int) ceil($results->pagination->getTotalItems() / $itemsPerPage);
        $parameters['itemsPerPage'] = $itemsPerPage;
        $headers = ['Accept' => $mimeType];

        $response = new StreamedResponse();
        $response->setCallback(function () use ($parameters, $pageNumber, $headers, $resource) {
            $page = 0;
            do {
                if (++$page > 1) {
                    $parameters['page'] = $page;
                    $parameters['context']['no_headers'] = true;
                }
                $resp = $this->client->request($resource, null, null, Request::METHOD_GET, ['query' => $parameters, 'headers' => $headers]);
                if (200 !== $resp->getStatusCode()) {
                    // Avoid multiple API call on failure
                    break;
                }

                $content = (string) $resp->getContent();
                echo $content;
                flush();
            } while ($page < $pageNumber && '' !== trim($content));
        });

        $response->headers->set('Content-Disposition', \sprintf('inline; filename=%s', $filename));
        $response->headers->set('Content-Type', \sprintf('%s; charset=utf-8', $mimeType));

        return $response;
    }
}

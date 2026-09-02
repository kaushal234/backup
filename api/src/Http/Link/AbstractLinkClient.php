<?php

declare(strict_types=1);

namespace App\Http\Link;

use App\DataProcessor\RealClassNameTrait;
use App\Link\SourceProvider\SourceProvider;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AbstractLinkClient
{
    use RealClassNameTrait;

    public function __construct(
        private readonly SourceProvider $sourceProvider,
    ) {
    }

    protected function query(HttpClientInterface $client, string $query, array $variables = []): array
    {
        $options = (new HttpOptions())->setJson(['query' => $query, 'variables' => (object) $variables]);

        $response = $client
            ->request('POST', '', $options->toArray())
            ->toArray();

        return \array_key_exists('errors', $response) ? throw new BadRequestException($response['errors'][0]['message']) : $response;
    }

    protected function getFunction(string $class, string $action = 'collection'): string
    {
        return \sprintf('%s_%s', $this->sourceProvider->getResourceSourceProvider($class)->getService(), 'collection' === $action ? 'findByFilter' : 'save');
    }
}

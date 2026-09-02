<?php

declare(strict_types=1);

namespace ApiBundle;

use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Event\BaanDatabaseQueryEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class Client.
 */
class Client
{
    final public const REQUEST_DELETE = 'DELETE';
    final public const REQUEST_POST = 'POST';
    final public const REQUEST_PUT = 'PUT';
    final public const REQUEST_GET = 'GET';

    private HttpClientInterface $tldApiClient;

    public function __construct(
        private readonly Security $security,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly KernelInterface $kernel,
        private readonly HttpClientInterface $httpClient,
        private readonly ?array $options,
    ) {
        $this->tldApiClient = new CachingHttpClient(
            $this->httpClient->withOptions($this->options ?? []),
            new FilesystemTagAwareAdapter('', 0, $this->kernel->getCacheDir().'/http_client_cache'),
            $this->options ?? [],
        );
    }

    public function __call($method, array $arguments)
    {
        switch (true) {
            case 0 === mb_strpos((string) $method, 'find'):
                $operation = 'find';
                break;
            case 0 === mb_strpos((string) $method, 'search'):
                $operation = 'search';
                break;
            case 0 === mb_strpos((string) $method, 'remove'):
                $operation = 'remove';
                break;
            case 0 === mb_strpos((string) $method, 'save'):
                $operation = 'save';
                break;
            default:
                throw new \LogicException(\sprintf('Unknown method: "%s"', $method));
        }

        $resource = $this->getSnakeCase(mb_substr((string) $method, mb_strlen($operation)));

        array_unshift($arguments, $resource);

        return \call_user_func_array([$this, $operation], $arguments);
    }

    public function get($entryPoint, array $options = [])
    {
        return $this->doClientRequest(self::REQUEST_GET, $entryPoint, $options);
    }

    public function post($entryPoint, array $options = [])
    {
        return $this->doClientRequest(self::REQUEST_POST, $entryPoint, $options);
    }

    public function put($entryPoint, array $options = [])
    {
        return $this->doClientRequest(self::REQUEST_PUT, $entryPoint, $options);
    }

    public function request($resource, $id = null, $operation = null, $method = self::REQUEST_POST, array $options = [])
    {
        if (empty($id) && empty($operation)) {
            $entryPoint = \sprintf('/%s', $resource);
        } else {
            $entryPoint = \sprintf('/%s/%s/%s', $resource, $id, $operation);
        }

        $options = $this->withCache($options);

        return $this->tldApiClient->request($method, $entryPoint, $this->withToken($options));
    }

    /**
     * @param string     $resource
     * @param string|int $id
     *
     * @return ApiData|string
     */
    public function find($resource, $id, array $options = [])
    {
        $entryPoint = \sprintf('/%s/%s', $resource, $id);
        $body = $this->doClientRequest(self::REQUEST_GET, $entryPoint, $options);

        return true === ($options['raw_results'] ?? null) ? $body : new ApiData($body);
    }

    /**
     * @param string $resource
     *
     * @return HydraCollection
     */
    public function findBy($resource, array $query = [], array $orders = [], $options = [])
    {
        if ([] !== $orders) {
            if (array_keys($orders) === range(0, \count($orders) - 1)) {
                $orders = array_fill_keys($orders, '');
            }

            foreach ($orders as $field => $direction) {
                $query[\sprintf('order[%s]', $field)] = $direction;
            }
        }
        $options['query'] = $query;

        return $this->search($resource, $options);
    }

    /**
     * @param string $resource
     * @param array  $options
     *
     * @return HydraCollection|\Generator
     */
    public function findAll($resource, array $query = [], array $orders = [], $options = [])
    {
        $page = 0;
        do {
            if (++$page > 1) {
                $query['page'] = $page;
            }
            $collection = $this->findBy($resource, $query, $orders, $options);
            foreach ($collection as $row) {
                yield $row;
            }
        } while ($collection->pagination->hasNextPage());
    }

    /**
     * @param string $resource
     * @param array  $query
     * @param array  $options
     */
    public function findOneBy($resource, $query, $options = [])
    {
        $options['query'] = $query;
        $res = $this->search($resource, $options);

        if (1 !== $res->pagination->getTotalItems()) {
            throw new \RangeException(\sprintf('Found %s %s when 1 was expected.', $res->pagination->getTotalItems(), $resource));
        }

        return $res[0];
    }

    /**
     * This method shouldn't be used directly if you don't need to set something to client HTTP
     * Use findBy method instead.
     *
     * @param string $resource
     *
     * @return HydraCollection|string
     */
    public function search($resource, array $options = [])
    {
        $entryPoint = 0 === mb_strpos($resource, '/') ? $resource : '/'.$resource;

        $body = $this->doClientRequest(self::REQUEST_GET, $entryPoint, $options);

        return true === ($options['raw_results'] ?? null) ? $body : new HydraCollection($body);
    }

    public function save($resource, $data, array $options = [])
    {
        if ($data instanceof ApiData) {
            $data = $data->toArray();
        }

        $options = array_merge($options, ['json' => $data]);

        if (isset($data['@id'])) {
            return $this->doClientRequest(self::REQUEST_PUT, $data['@id'], $options);
        }

        $entryPoint = 0 !== mb_strpos((string) $resource, '/') ? '/'.$resource : $resource;

        return $this->doClientRequest(self::REQUEST_POST, $entryPoint, $options);
    }

    public function remove($resource, $id, array $options = [])
    {
        $entryPoint = \sprintf('/%s/%s', $resource, $id);

        return $this->doClientRequest(self::REQUEST_DELETE, $entryPoint, $options);
    }

    public static function extractId($item)
    {
        $iri = \is_array($item) ? $item['@id'] : $item;
        [$void, $resource, $id] = explode('/', (string) $iri);

        return $id;
    }

    public function doClientRequest($method, $entryPoint, $options)
    {
        $options = $this->withCache($options);

        $options['query'] = $this->normalizeQueryString($options['query'] ?? []);
        $response = $this->tldApiClient->request($method, $entryPoint, $this->withToken($options));

        $baanQueriesCount = (int) ($response->getHeaders()['x-baan-queries'][0] ?? '0');
        if ($baanQueriesCount > 0) {
            $this->eventDispatcher->dispatch(new BaanDatabaseQueryEvent());
        }

        $content = null;
        foreach ($this->tldApiClient->stream($response) as $chunk) {
            if ($chunk->isLast()) {
                $content = $response->getContent();
            }
        }

        if ('' === $content) {
            return null;
        }

        return json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
    }

    protected function withToken(array $options = []): array
    {
        $user = $this->security->getUser();

        // Mapping of options params previously used in Guzzle
        if (isset($options['form_params'])) {
            $options['body'] = $options['form_params'];
            unset($options['form_params']);
        }
        if (isset($options['raw_results'])) {
            $options['body']['raw_results'] = $options['raw_results'];
            unset($options['raw_results']);
        }

        if (!$user instanceof User) {
            return $options;
        }

        return array_merge($options, [
            'auth_bearer' => $user->getToken(),
        ]);
    }

    private function getSnakeCase($str): string
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', (string) $str, $matches);
        $ret = $matches[0];

        foreach ($ret as &$match) {
            $match = $match === mb_strtoupper((string) $match) ? mb_strtolower($match) : lcfirst((string) $match);
        }

        return implode('_', $ret);
    }

    /**
     * Ensure that query string are sent in a the same order independently of how they are requested in the code
     * to improve cache hits.
     */
    private function normalizeQueryString(array $query): array
    {
        $sorted = array_filter($query, static fn (string $key) => false === mb_strpos($key, 'order['), \ARRAY_FILTER_USE_KEY);

        // Ordering should be preserved
        $preserved = array_filter($query, static fn (string $key) => false !== mb_strpos($key, 'order['), \ARRAY_FILTER_USE_KEY);

        ksort($sorted);

        return array_merge($sorted, $preserved);
    }

    private function withCache(array $options): array
    {
        $cache = $options['cache'] ?? false;
        $reload = $options['reload'] ?? false;

        if (isset($options['cache'])) {
            unset($options['cache']);
        }

        if (isset($options['reload'])) {
            unset($options['reload']);
        }

        if ($this->kernel->isDebug()) {
            $cache = false;
        }

        $options['extra']['no_cache'] = !$cache;
        if ($reload) {
            $options['headers']['Cache-Control'] = 'no-cache';
        }

        return $options;
    }
}

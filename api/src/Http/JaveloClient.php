<?php

declare(strict_types=1);

namespace App\Http;

use App\Javelo\DataTransformer\GroupPatchDataTransformer;
use App\Javelo\DataTransformer\UserPatchDataTransformer;
use App\Javelo\DataTransformer\UserPostDataTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class JaveloClient
{
    private const USERS_URL = 'Users';
    private const GROUPS_URL = 'Groups';
    private const HEADERS = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/scim+json',
    ];

    public function __construct(
        private readonly HttpClientInterface $javeloClient,
        private readonly UserPatchDataTransformer $userPatchDataTransformer,
        private readonly UserPostDataTransformer $userPostDataTransformer,
        private readonly GroupPatchDataTransformer $groupPatchDataTransformer,
    ) {
    }

    public function doUserRequest(array $options, ?string $id = null, string $method = Request::METHOD_GET): ResponseInterface
    {
        return $this->doRequest(self::USERS_URL, $options, $method, $id, $this->userPostDataTransformer, $this->userPatchDataTransformer);
    }

    public function doGroupRequest(array $options, ?string $id = null, string $method = Request::METHOD_GET): ResponseInterface
    {
        return $this->doRequest(self::GROUPS_URL, $options, $method, $id, null, $this->groupPatchDataTransformer);
    }

    private function doRequest(string $baseUrl, array $options, string $method, ?string $id = null, ?DataTransformerInterface $postTransformer = null, ?DataTransformerInterface $patchTransformer = null): ResponseInterface
    {
        $url = $baseUrl;
        if (null !== $id) {
            $url = \sprintf('%s/%s', $url, $id);
        }

        if (\in_array($method, [Request::METHOD_POST, Request::METHOD_PATCH], true)) {
            $options['headers'] = self::HEADERS;
            if (Request::METHOD_POST === $method && $postTransformer) {
                $options['body'] = json_encode($postTransformer->transform($options['body']));
            } elseif ($patchTransformer) {
                $options['body'] = json_encode($patchTransformer->transform($options['body']));
            }
        }

        if (isset($options['filter']) || isset($options['count']) || isset($options['startIndex'])) {
            $queryString = http_build_query($options, '', '&', \PHP_QUERY_RFC3986);
            $url .= '?'.$queryString;
            unset($options['filter'], $options['count'], $options['startIndex']);
        }

        return $this->javeloClient->request($method, $url, $options);
    }
}

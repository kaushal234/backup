<?php

declare(strict_types=1);

namespace App\Util;

use Symfony\Component\Routing\RouterInterface;

class IriToId
{
    private readonly RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getId(string $iri)
    {
        try {
            $parameters = $this->router->match($iri);
        } catch (\Exception $exception) {
            return null;
        }

        if (!isset($parameters['_api_resource_class'])) {
            return null;
        }

        return $parameters['id'] ?? null;
    }
}

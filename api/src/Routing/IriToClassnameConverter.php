<?php

declare(strict_types=1);

namespace App\Routing;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\RouterInterface;

class IriToClassnameConverter
{
    private readonly RouterInterface $router;

    /**
     * IriToClassnameConverter constructor.
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function convert(string $iri): string
    {
        try {
            $parameters = $this->router->match($iri);
            if (!isset($parameters['_api_resource_class'])) {
                throw new \Exception('No API Resource class on route');
            }
            $resourceClass = $parameters['_api_resource_class'];
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException(\sprintf("Can't find any resource matching %s", $iri), $exception);
        }

        return $resourceClass;
    }
}

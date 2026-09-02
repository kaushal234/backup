<?php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    private $decorated;

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        /**
         * @var string         $path
         * @var Model\PathItem $pathItem
         */
        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            if ('/me' === $path) {
                /** @var Model\Operation $operation */
                $operation = $pathItem->getGet();
                $openApi->getPaths()->addPath($path, $pathItem->withGet($operation->withParameters([])));
            }

            if (false !== mb_strpos($path, 'fileId')) {
                $parameters = $pathItem->getParameters();
                $parameters[] = new Parameter('fileId', 'path', 'File ID', true, false, false, ['type' => 'string']);
                $openApi->getPaths()->addPath($path, $pathItem->withParameters($parameters));
            }
        }

        return $openApi;
    }
}

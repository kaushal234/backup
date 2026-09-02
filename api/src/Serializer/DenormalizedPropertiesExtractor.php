<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\JsonLd\Serializer\ItemNormalizer;
use ApiPlatform\State\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class DenormalizedPropertiesExtractor
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $contextBuilder,
        private readonly RouterInterface $router,
        private readonly ItemNormalizer $itemNormalizer
    ) {
    }

    public function extract(string $iri, string $method): array
    {
        $this->router->getContext()->setMethod($method);
        $attributes = $this->router->match($iri);

        $fakeRequest = Request::create($iri, $method, [], [], [], []);
        foreach ($attributes as $key => $value) {
            $fakeRequest->attributes->set($key, $value);
        }

        $resourceClass = $fakeRequest->attributes->get('_api_resource_class');
        $context = $this->contextBuilder->createFromRequest($fakeRequest, false) + ['api_denormalize' => true];

        $reflectionMethod = (new \ReflectionClass($this->itemNormalizer))->getMethod('getAllowedAttributes');
        $reflectionMethod->setAccessible(true);

        $denormalizedProperties = $reflectionMethod->invoke($this->itemNormalizer, $resourceClass, $context, true);

        foreach ($denormalizedProperties as $key => $value) {
            if (str_starts_with($value, '@')) {
                unset($denormalizedProperties[$key]);
            }
        }

        return array_values(array_unique(array_map(static fn (string $propertyName) => preg_replace('#Override$#', '', $propertyName), $denormalizedProperties)));
    }
}

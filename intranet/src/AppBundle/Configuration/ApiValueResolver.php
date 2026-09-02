<?php

declare(strict_types=1);

namespace AppBundle\Configuration;

use ApiBundle\Client;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ApiValueResolver implements ValueResolverInterface
{
    private readonly Client $client;
    private readonly Inflector $inflector;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->inflector = InflectorFactory::create()->build();
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $resolver = $argument->getAttributes()[0];
        if (!$resolver instanceof ApiValueResolverAttribute || !\in_array($argument->getType(), [ApiData::class, HydraCollection::class], true)) {
            throw new \InvalidArgumentException('This resolver should be called to hydrate only ApiData or HydraCollection.');
        }

        $parameters = $resolver->parameters;
        $resource = null;
        $name = $this->inflector->tableize($argument->getName());
        switch (true) {
            case HydraCollection::class === $argument->getType():
                $path = $parameters['resource'] ?? $name;
                $resource = $this->client->findBy($path, $parameters['filters'] ?? []);
                break;
            case ApiData::class === $argument->getType():
                $path = $parameters['resource'] ?? $this->inflector->pluralize($name);
                $id = $request->attributes->get($parameters['id'] ?? 'id');

                if (null === $id) {
                    return [];
                }

                $resource = $this->client->find($path, $id, ['query' => $parameters['filters'] ?? []]);
                break;
        }

        $allowedTypes = $parameters['allowedTypes'] ?? [$argument->getName()];
        if (null !== $this->getPayloadType($resource) && !\in_array(lcfirst($this->getPayloadType($resource)), $allowedTypes, true)) {
            throw new \InvalidArgumentException(\sprintf('You request a payload of type %s but you have set variable name as  one of [%s]. These two should match.', $this->getPayloadType($resource), implode(', ', $allowedTypes)));
        }

        return [$resource];
    }

    private function getPayloadType(ApiData|HydraCollection $resource): ?string
    {
        if ($resource instanceof ApiData) {
            return $resource['@type'];
        }

        return (\count($resource) > 1) ? $this->inflector->pluralize($resource->first()['@type']) : null;
    }
}

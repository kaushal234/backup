<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

class IriToResource
{
    private readonly ResourceMetadataCollectionFactoryInterface $metadataFactory;

    private readonly RouterInterface $router;

    /**
     * IriToResource constructor.
     */
    public function __construct(ResourceMetadataCollectionFactoryInterface $metadataFactory, RouterInterface $router)
    {
        $this->metadataFactory = $metadataFactory;
        $this->router = $router;
    }

    public function __invoke($iri, array $options)
    {
        try {
            $parameters = $this->router->match($iri);
        } catch (ExceptionInterface $exceptionInterface) {
            throw new UnprocessableEntityHttpException(\sprintf('No route matches "%s".', $iri), $exceptionInterface);
        }

        if (
            !isset($parameters['_api_resource_class'])
        ) {
            throw new UnprocessableEntityHttpException(\sprintf('No resource associated to "%s".', $iri));
        }

        $metadata = $this->metadataFactory->create($parameters['_api_resource_class']);

        /** @var ApiResource $apiResource */
        $apiResource = $metadata->getIterator()->current();

        return $apiResource->getShortName();
    }
}

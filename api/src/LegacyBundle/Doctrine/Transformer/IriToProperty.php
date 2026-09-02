<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\ION\DataProvider\AbstractIONDataProvider;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;

class IriToProperty extends ObjectToProperty
{
    private readonly IriConverterInterface $iriConverter;
    private readonly RouterInterface $router;
    private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory;

    public function __construct(IriConverterInterface $iriConverter, PropertyAccessorInterface $propertyAccessor, RouterInterface $router, ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory)
    {
        $this->iriConverter = $iriConverter;
        parent::__construct($propertyAccessor);
        $this->router = $router;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    public function __invoke($iri, array $options)
    {
        $parameters = $this->router->match($iri);
        if (isset($parameters['_api_resource_class'])) {
            $metadata = $this->resourceMetadataFactory->create($parameters['_api_resource_class']);

            /** @var ApiResource $apiResource */
            $apiResource = $metadata->getIterator()->current();
            $extraProperties = $apiResource->getExtraProperties();
            if (isset($extraProperties[AbstractIONDataProvider::ION_RESOURCE_ATTRIBUTE])) {
                // we don't want to fetch the resource when it is an ION resource, it is a performance issue
                // and there is no reason to persist an ION value in the legacy so far
                return 0;
            }
        }

        try {
            $item = $this->iriConverter->getResourceFromIri($iri, ['fetch_data' => false]);

            return @parent::__invoke($item, $options);
        } catch (InvalidArgumentException|EntityNotFoundException $e) {
            throw new UnprocessableEntityHttpException(\sprintf('The resource "%s" does not exists', $iri), $e);
        } catch (NoSuchPropertyException $noSuchPropertyException) {
            if (isset($options['allow_missing']) && $options['allow_missing']) {
                return 0;
            }

            throw $noSuchPropertyException;
        }
    }
}

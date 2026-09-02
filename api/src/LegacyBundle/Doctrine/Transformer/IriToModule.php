<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;

class IriToModule extends IriToResource
{
    private array $mapping = [];

    /**
     * IriToResource constructor.
     */
    public function __construct(ResourceMetadataCollectionFactoryInterface $metadataFactory, RouterInterface $router, ParameterBagInterface $parameters)
    {
        parent::__construct($metadataFactory, $router);

        $this->mapping = (array) $parameters->get('legacy.module_mapping');
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($iri, array $options)
    {
        $resource = parent::__invoke($iri, $options);

        if (!isset($this->mapping[mb_strtolower((string) $resource)])) {
            return $resource;
        }

        return $this->mapping[mb_strtolower((string) $resource)];
    }
}

<?php

declare(strict_types=1);

namespace Shared\Provider;

use ApiBundle\Client;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;

abstract class AbstractProvider
{
    protected Client $client;
    protected Serializer $serializer;

    public function __construct()
    {
        global $kernel;
        $this->client = $kernel->getContainer()->get(Client::class);

        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $this->serializer = new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer($classMetadataFactory, null, null, new PhpDocExtractor()),
            new GetSetMethodNormalizer()
        ]);
    }
}
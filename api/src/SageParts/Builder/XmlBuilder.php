<?php

declare(strict_types=1);

namespace App\SageParts\Builder;

use App\SageParts\Factory\PriceAndAvailabilityFactory;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class XmlBuilder
{
    private SerializerInterface $serializer;
    private PriceAndAvailabilityFactory $priceAndAvailabilityFactory;

    public function __construct(SerializerInterface $serializer, PriceAndAvailabilityFactory $priceAndAvailabilityFactory)
    {
        $this->serializer = $serializer;
        $this->priceAndAvailabilityFactory = $priceAndAvailabilityFactory;
    }

    public function build(string $identifier): string
    {
        return $this->serializer->serialize($this->priceAndAvailabilityFactory->create($identifier), 'xml', [
            XmlEncoder::ROOT_NODE_NAME => 'PriceAndAvailability',
            XmlEncoder::REMOVE_EMPTY_TAGS => true,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
        ]);
    }
}

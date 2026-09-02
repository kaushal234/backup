<?php

declare(strict_types=1);

namespace App\ION\DataProvider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use App\Client\Exception\SoapException;
use App\ION\Client\IONSoapConfigurator;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\ItemProviderIdentifierInterface;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class IONItemDataProvider extends AbstractIONDataProvider
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $resourceClass = $operation->getClass();
        if (null === $resourceClass) {
            return null;
        }

        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($resourceClass);

        $ionResource = $resourceSourceProvider->getResource();
        $ionOperation = $resourceSourceProvider->getItemReadOperation();

        $dataArea = $this->normalizer->normalize($context[DataAreaFilter::CONTEXT_DATA_AREA_KEY] ?? []);
        $dataArea = array_merge($resourceSourceProvider->getDataAreaFilter(), $dataArea ?? []);

        $controlArea = $this->addSelectionFilter($ionResource, $context);

        $client = $this->soapClientFactory->createClient(IONSoapConfigurator::CLIENT_NAME, $ionResource);

        if (class_exists($resourceClass) && \in_array(ItemProviderIdentifierInterface::class, class_implements($resourceClass), true)) {
            $uriVariables = $resourceClass::getIdentifier($uriVariables);
        }

        $parameters[self::DATA_AREA] = [$ionResource => $uriVariables + $dataArea];
        // todo: refacto here
        if ([] !== $controlArea) {
            $parameters[self::CONTROL_AREA] = $controlArea;
        }

        try {
            $client->{$ionOperation}($parameters);
        } catch (\SoapFault $exception) {
            throw SoapException::createFromSoapFault($resourceClass, $exception);
        }

        if (!isset($context[AbstractNormalizer::GROUPS])) {
            $context = [...$context, ...$operation->getNormalizationContext()];
        }

        return $this->serializer->deserialize(
            $client->__getLastResponse(),
            $resourceClass,
            IONXmlDecoder::FORMAT,
            $context + self::generateExtraContext($ionOperation, $ionResource) + ['operation_type' => Get::class]
        );
    }
}

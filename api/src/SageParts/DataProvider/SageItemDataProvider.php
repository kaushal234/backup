<?php

declare(strict_types=1);

namespace App\SageParts\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Client\Exception\SoapException;
use App\Client\SoapClientFactoryInterface;
use App\SageParts\Builder\Parameters;
use App\SageParts\Client\SageSoapConfigurator;
use App\SageParts\Serializer\Encoder\SageXmlEncoder;
use App\SageParts\SourceProvider\SourceProvider;
use Symfony\Component\Serializer\SerializerInterface;

class SageItemDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly Parameters $parameters,
        private readonly SourceProvider $sourceProvider,
        private readonly SoapClientFactoryInterface $soapClientFactory,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $class = $operation->getClass();
        $operation = $this->sourceProvider->getResourceSourceProvider($class)->getItemReadOperation();

        $client = $this->soapClientFactory->createClient(SageSoapConfigurator::CLIENT_NAME, $operation);

        try {
            $client->{$operation}($this->parameters->getParameters($uriVariables['alvestId']));
        } catch (\SoapFault $exception) {
            throw SoapException::createFromSoapFault($class, $exception);
        }

        return $this->serializer->deserialize(
            $client->__getLastResponse(),
            $class,
            SageXmlEncoder::FORMAT,
            $context
        );
    }
}

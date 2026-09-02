<?php

declare(strict_types=1);

namespace App\ION\DataProcessor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Client\Exception\SoapException;
use App\Client\SoapClientFactoryInterface;
use App\DataProcessor\RealClassNameTrait;
use App\ION\Client\IONSoapConfigurator;
use App\ION\DataProvider\AbstractIONDataProvider;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use App\ION\SourceProvider\SourceProvider;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @template T
 */
class IONDataProcessor implements ProcessorInterface
{
    use RealClassNameTrait;
    /** @var string */
    final public const ION_SYNC = 'ion:sync';

    public function __construct(
        private readonly SoapClientFactoryInterface $soapClientFactory,
        private readonly SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
        private readonly SourceProvider $sourceProvider,
    ) {
    }

    /**
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($this->getRealClassName($data));

        if (null === ($ionOperation = null === ($context['previous_data'] ?? null) ? $resourceSourceProvider->getCreateOperation() : $resourceSourceProvider->getUpdateOperation())) {
            return $data;
        }

        $resource = $resourceSourceProvider->getResource();

        if (\is_array($ionOperation)) {
            $resource = current($ionOperation);
            $ionOperation = current(array_keys($ionOperation));
        }

        $response = $this->callIONOperation($ionOperation, $resourceSourceProvider, $data, $resource);

        if (null === $response) {
            return $data;
        }

        if ($resourceSourceProvider->deserializeAfterPersist()) {
            $outputClass = null !== $operation->getOutput() ? $operation->getOutput()['class'] : $context['resource_class'];

            return $this->serializer->deserialize(
                $response,
                $outputClass,
                IONXmlDecoder::FORMAT,
                $context + $operation->getNormalizationContext() + AbstractIONDataProvider::generateExtraContext($ionOperation, $resource)
            );
        }

        return $data;
    }

    private function callIONOperation(string $operation, ResourceSourceProviderInterface $resourceSourceProvider, $data, ?string $resource = null): ?string
    {
        $ionResource = $resource ?? $resourceSourceProvider->getResource();
        $IONSoapClient = $this->soapClientFactory->createClient(IONSoapConfigurator::CLIENT_NAME, $ionResource);

        $dataArea = $this->normalizer->normalize($data, null, [AbstractNormalizer::GROUPS => [self::ION_SYNC]]);
        $dataArea = array_merge($resourceSourceProvider->getDataAreaFilter(), $dataArea ?? []);

        try {
            $IONSoapClient->{$operation}([AbstractIONDataProvider::DATA_AREA => [$ionResource => $dataArea]]);

            return $IONSoapClient->__getLastResponse();
        } catch (\SoapFault $exception) {
            throw SoapException::createFromSoapFault($this->getRealClassName($data), $exception);
        }
    }
}

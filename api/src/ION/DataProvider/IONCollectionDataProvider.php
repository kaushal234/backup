<?php

declare(strict_types=1);

namespace App\ION\DataProvider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Client\Exception\SoapException;
use App\ION\Client\IONSoapConfigurator;
use App\ION\Event\IONPreNormalizeEvent;
use App\ION\Filter\DataAreaFilter;
use App\ION\Filter\IONFilter;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class IONCollectionDataProvider extends AbstractIONDataProvider
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $resourceClass = $operation->getClass();
        if (null === $resourceClass) {
            return null;
        }
        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($resourceClass);

        $ionResource = $resourceSourceProvider->getResource();
        $ionOperation = $resourceSourceProvider->getCollectionReadOperation();

        $controlArea = $this->addSelectionFilter($ionResource, $context);

        if (filter_var($context['filters']['pagination'] ?? true, \FILTER_VALIDATE_BOOLEAN) && ($operation->getPaginationEnabled() ?? true)) {
            $controlArea['maxNumberOfObjects'] = (int) ($context['filters']['itemsPerPage'] ?? $this->defaultItemsPerPage);
        }

        if (null === $logicalExpression = $context[IONFilter::CONTEXT_LOGICAL_EXPRESSION_KEY] ?? null) {
            $logicalExpressionBuilder = $this->logicalExpressionBuilderFactory->create($ionResource);
            $logicalExpression = $logicalExpressionBuilder->getLogicalExpression();
        }

        $this->eventDispatcher->dispatch($event = new IONPreNormalizeEvent($resourceClass, $ionResource, $logicalExpression));
        $context[DataAreaFilter::CONTEXT_DATA_AREA_KEY] = array_merge($context[DataAreaFilter::CONTEXT_DATA_AREA_KEY] ?? [], $event->getDataArea());

        $dataArea = $this->normalizer->normalize($context[DataAreaFilter::CONTEXT_DATA_AREA_KEY]);

        $this->validator->validate($logicalExpression);
        $controlArea['Filter']['LogicalExpression'] = $this->normalizer->normalize($logicalExpression);

        $parameters[self::CONTROL_AREA] = $controlArea;
        $ionResourceParameters = array_merge($resourceSourceProvider->getDataAreaFilter(), $dataArea ?? []);
        if ([] !== $ionResourceParameters) {
            $parameters[self::DATA_AREA] = [$ionResource => $ionResourceParameters];
        }

        $client = $this->soapClientFactory->createClient(IONSoapConfigurator::CLIENT_NAME, $ionResource);

        try {
            $client->{$ionOperation}($parameters);
        } catch (\SoapFault $exception) {
            throw SoapException::createFromSoapFault($resourceClass, $exception);
        }

        if (!isset($context[AbstractNormalizer::GROUPS])) {
            $context = array_merge($context, $operation->getNormalizationContext());
        }

        return $this->serializer->deserialize(
            $client->__getLastResponse(),
            \sprintf('%s[]', $resourceClass),
            IONXmlDecoder::FORMAT,
            $context + self::generateExtraContext($ionOperation, $ionResource) + ['operation_type' => GetCollection::class]
        );
    }
}

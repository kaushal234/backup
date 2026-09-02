<?php

declare(strict_types=1);

namespace App\ION\DataProvider;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Client\SoapClientFactoryInterface;
use App\ION\Client\Request\LogicalExpressionBuilderFactory;
use App\ION\Client\Request\SelectionBuilder;
use App\ION\Filter\SelectionFilter;
use App\ION\Serializer\Denormalizer\UppercasePropertiesDenormalizer;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use App\ION\SourceProvider\SourceProvider;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractIONDataProvider implements ProviderInterface
{
    final public const CONTROL_AREA = 'ControlArea';
    final public const DATA_AREA = 'DataArea';

    /** @var string */
    final public const ION_RESOURCE_ATTRIBUTE = '_ion_resource';

    protected SourceProvider $sourceProvider;
    protected SerializerInterface $serializer;
    protected NormalizerInterface $normalizer;
    protected SoapClientFactoryInterface $soapClientFactory;
    protected ValidatorInterface $validator;
    protected ManagerRegistry $managerRegistry;
    protected LogicalExpressionBuilderFactory $logicalExpressionBuilderFactory;
    protected EventDispatcherInterface $eventDispatcher;
    protected int $defaultItemsPerPage;
    protected SelectionBuilder $selectionBuilder;

    public function __construct(
        SourceProvider $sourceProvider,
        SerializerInterface $serializer,
        NormalizerInterface $normalizer,
        SoapClientFactoryInterface $soapClientFactory,
        ValidatorInterface $validator,
        ManagerRegistry $managerRegistry,
        LogicalExpressionBuilderFactory $logicalExpressionBuilderFactory,
        SelectionBuilder $selectionBuilder,
        EventDispatcherInterface $eventDispatcher,
        int $defaultItemsPerPage
    ) {
        $this->sourceProvider = $sourceProvider;
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
        $this->soapClientFactory = $soapClientFactory;
        $this->validator = $validator;
        $this->managerRegistry = $managerRegistry;
        $this->logicalExpressionBuilderFactory = $logicalExpressionBuilderFactory;
        $this->selectionBuilder = $selectionBuilder;
        $this->eventDispatcher = $eventDispatcher;
        $this->defaultItemsPerPage = $defaultItemsPerPage;
    }

    public static function generateExtraContext(string $ionOperation, string $ionResource): array
    {
        return [
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            IONXmlDecoder::ION_OPERATION => $ionOperation,
            self::ION_RESOURCE_ATTRIBUTE => $ionResource,
            UppercasePropertiesDenormalizer::SERIALIZER_FORCE_LOWERCASE => true,
        ];
    }

    public function addSelectionFilter(string $ionResource, array $context = [], array $controlArea = []): array
    {
        if (null !== $selectionFields = $context[SelectionFilter::CONTEXT_SELECTION_AREA_KEY] ?? null) {
            $controlArea[SelectionBuilder::SELECTION_NODE] = $this->selectionBuilder->build($ionResource, $selectionFields);
        }

        return $controlArea;
    }
}

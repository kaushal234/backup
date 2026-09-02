<?php

declare(strict_types=1);

namespace App\Link\QueryBuilder;

use App\DataProcessor\RealClassNameTrait;
use App\Link\Mapping\Mapper\FieldMapper;
use App\Link\Resource\LinkResourceInterface;
use App\Link\SourceProvider\SourceProvider;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GraphQLMutationBuilder
{
    use RealClassNameTrait;
    final public const LINK_NORMALIZATION_GROUP = 'link:synchronization';

    private array $transformers = [];

    public function __construct(
        private readonly SourceProvider $sourceProvider,
        private readonly FieldMapper $fieldMapper,
        private readonly NormalizerInterface $normalizer,
        #[AutowireIterator(tag: 'link.field_transformer')] iterable $transformers
    ) {
        foreach ($transformers as $transformer) {
            $this->transformers[$transformer::class] = $transformer;
        }
    }

    public function getMutation(object $data, array $groups = [], array $extraProperties = []): string
    {
        if (!$data instanceof LinkResourceInterface) {
            throw new UnprocessableEntityHttpException('Object should be an instance of LinkResourceInterface.');
        }

        $class = $this->getRealClassName($data);
        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($class);

        $reflectionClass = false === (new \ReflectionObject($data))->getParentClass() ? new \ReflectionClass($data::class) : (new \ReflectionObject($data))->getParentClass();
        $mapping = $this->fieldMapper->getMapping($reflectionClass);

        $payload = [];
        foreach ($this->normalizer->normalize($data, null, [AbstractNormalizer::GROUPS => [...$groups, self::LINK_NORMALIZATION_GROUP]]) as $property => $value) {
            if (null === $value) {
                continue;
            }

            if (\array_key_exists($property, $mapping)) {
                if (empty($mapping[$property]['transformer'])) {
                    foreach ($mapping[$property]['fields'] as $field) {
                        $payload = [...$payload, $field => $value];
                    }

                    continue;
                }

                foreach ($mapping[$property]['fields'] as $field) {
                    $payload = [...$payload, ...$this->transformValue($value, $mapping[$property], $field)];

                    /** @var \ReflectionNamedType $reflectionType */
                    $reflectionType = (new \ReflectionProperty($class, $property))->getType();

                    $propertyClass = $reflectionType->getName();
                    if (new $propertyClass() instanceof LinkResourceInterface && \is_array($value)) {
                        $payload[$field]['id'] = $value['linkId'];
                    }
                }
            }
        }

        foreach ($extraProperties as $key => $value) {
            $payload[$key] = $value;
        }

        return \sprintf(
            <<<'GRAPHQL'
                mutation {
                  %s_save(params:
                    {
                        %s
                        fieldsAndValues: %s
                    }
                  ) {%s}
                }
                GRAPHQL,
            $resourceSourceProvider->getService(),
            null === $data->getLinkId() ? '' : \sprintf('id: %s,', $data->getLinkId()),
            preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode($payload)),
            $resourceSourceProvider->getReturnedFields(),
        );
    }

    public function getArchiveMutation(object $data): string
    {
        if (!$data instanceof LinkResourceInterface || null === $data->getLinkId()) {
            throw new UnprocessableEntityHttpException('Object should be an instance of LinkResourceInterface and have linkId property set.');
        }

        $class = $this->getRealClassName($data);
        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($class);

        return \sprintf(
            <<<'GRAPHQL'
                mutation {
                  %s_save(params:
                    {
                        id: %s,
                        fieldsAndValues: %s
                    }
                  ) {%s}
                }
                GRAPHQL,
            $resourceSourceProvider->getService(),
            $data->getLinkId(),
            preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode(['archived' => true])),
            $resourceSourceProvider->getReturnedFields(),
        );
    }

    private function transformValue($value, array $mappingConfig, string $field)
    {
        $transformers = $mappingConfig['transformer'];

        foreach ($transformers as $transformer) {
            if (!\array_key_exists($transformer, $this->transformers)) {
                continue;
            }
            $value = $this->transformers[$transformer]($value, $mappingConfig['options'], $field);
        }

        return $value;
    }
}

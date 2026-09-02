<?php

declare(strict_types=1);

namespace App\Maker;

use ApiPlatform\Hydra\Serializer\CollectionNormalizer;
use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\ION\SourceProvider\SourceProvider;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MakeFeature extends AbstractMaker
{
    public function __construct(
        private readonly SourceProvider $sourceProvider,
        private readonly ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory,
        private readonly IriConverterInterface $iriConverter,
        private readonly SchemaFactoryInterface $schemaFactory,
    ) {
    }

    public static function getCommandName(): string
    {
        return 'make:feature';
    }

    public static function getCommandDescription(): string
    {
        return 'generate the feature file and the json schema for a given api resource';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Creates a minimal feature file')
        ;
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $resources = $this->resourceNameCollectionFactory->create();

        $resourceClass = null;
        while (null === $resourceClass) {
            $question = new Question('Resource class');
            $question->setAutocompleterValues($resources);
            $resourceClass = $io->askQuestion($question);
        }

        $collectionUrl = $this->iriConverter->getIriFromResource($resourceClass, UrlGeneratorInterface::ABS_PATH, new GetCollection());

        $inflector = InflectorFactory::create()->build();
        $shortResourceName = Str::getShortClassName($resourceClass);
        $shortResourceNamePluralized = $inflector->pluralize($shortResourceName);
        $resourceHumanName = Str::asHumanWords($shortResourceName);
        $resourceHumanNamePluralized = Str::asHumanWords($shortResourceNamePluralized);
        $resourceCamelCase = Str::asSnakeCase($resourceHumanName);
        $resourceCamelCasePluralized = Str::asSnakeCase($resourceHumanNamePluralized);

        $namespaceParts = explode('\\', (string) $resourceClass);
        $folder = null;
        if (isset($namespaceParts[\count($namespaceParts) - 2]) && 'Entity' !== $namespaceParts[\count($namespaceParts) - 2]) {
            $folder = Str::asSnakeCase($namespaceParts[\count($namespaceParts) - 2]);
        }

        $schemaBasePath = 'tests/fixtures/json';

        try {
            $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($resourceClass);
        } catch (UnprocessableEntityHttpException $exception) {
            $resourceSourceProvider = null;
        }

        $templateParameters = [
            'collectionUrl' => $collectionUrl,
            'resourceHumanName' => $resourceHumanName,
            'resourceHumanNamePluralized' => $resourceHumanNamePluralized,
            'resourceCamelCase' => $resourceCamelCase,
            'resourceCamelCasePluralized' => $resourceCamelCasePluralized,
            'resourceClass' => $resourceClass,
        ];

        if (null !== $resourceSourceProvider) {
            $schemaBasePath .= '/ion';
            $generator->generateFile(
                \sprintf('features/ion/%s.feature', $resourceCamelCase),
                'resources/skeleton/resource_ion.feature.tpl.php',
                $templateParameters + [
                    'schemaBasePath' => $schemaBasePath,
                    'ionResource' => $resourceSourceProvider->getResource(),
                    'ionItemOperation' => $resourceSourceProvider->getItemReadOperation(),
                    'ionCollectionOperation' => $resourceSourceProvider->getCollectionReadOperation(),
                ]
            );
        } else {
            $generator->generateFile(
                \sprintf('features/%s/%s.feature', $folder, $resourceCamelCase),
                'resources/skeleton/resource.feature.tpl.php',
                $templateParameters + [
                    'schemaBasePath' => $schemaBasePath,
                ]
            );
        }

        $itemSchema = $this->schemaFactory->buildSchema($resourceClass, CollectionNormalizer::FORMAT, Schema::TYPE_OUTPUT, new Get());

        foreach ($itemSchema['definitions'] as &$definition) {
            $definition = $this->addMissingSchemaProperties($definition, $collectionUrl);
        }
        unset($definition);

        $generator->dumpFile(
            \sprintf('%s/%2$s/schemas/%2$s.json', $schemaBasePath, $resourceCamelCase),
            (string) json_encode($itemSchema, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)
        );

        $collectionSchema = $this->schemaFactory->buildSchema($resourceClass, CollectionNormalizer::FORMAT, Schema::TYPE_OUTPUT, new GetCollection());

        foreach ($collectionSchema['definitions'] as &$definition) {
            $definition = $this->addMissingSchemaProperties($definition, $collectionUrl);
        }
        unset($definition);

        $generator->dumpFile(
            \sprintf('%s/%s/schemas/%s.json', $schemaBasePath, $resourceCamelCase, $resourceCamelCasePluralized),
            (string) json_encode($collectionSchema, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)
        );

        $generator->writeChanges();
    }

    private function addMissingSchemaProperties(\ArrayObject $definition, string $collectionUrl): \ArrayObject
    {
        $definition['required'] = array_keys($definition['properties']);
        $definition['additionalProperties'] = false;
        foreach ($definition['properties'] as $property => &$schema) {
            if ('@id' === $property) {
                $schema['pattern'] = \sprintf('^%s/\d+$', $collectionUrl);
            }
            switch ($schema['type'] ?? null) {
                case 'object':
                    $schema = $this->addMissingSchemaProperties($schema, 'TODO');
                    break;
                case 'array':
                    $schema['minItems'] = 1;
                    break;
            }
        }
        unset($schema);

        return $definition;
    }
}

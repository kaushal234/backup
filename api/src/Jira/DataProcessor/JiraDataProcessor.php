<?php

declare(strict_types=1);

namespace App\Jira\DataProcessor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DataProcessor\RealClassNameTrait;
use App\Http\JiraTracteasyClient;
use App\Jira\Mapping\Mapper\FieldMapper;
use App\Jira\Resolver\JiraClientResolver;
use App\Jira\Resources\CommentResourceInterface;
use App\Jira\Resources\IssueByKeyInterface;
use App\Jira\Resources\IssueInterface;
use App\Jira\SourceProvider\SourceProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Workflow\Registry;

/**
 * @template T
 */
class JiraDataProcessor implements ProcessorInterface
{
    use RealClassNameTrait;

    private array $transformers = [];

    public function __construct(
        private readonly JiraClientResolver $clientResolver,
        private readonly SourceProvider $sourceProvider,
        private readonly FieldMapper $fieldMapper,
        private readonly DenormalizerInterface $denormalizer,
        private readonly EntityManagerInterface $entityManager,
        #[AutowireIterator(tag: 'jira.field_transformer')]
        iterable $transformers, private readonly Registry $registry
    ) {
        foreach ($transformers as $transformer) {
            $this->transformers[$transformer::class] = $transformer;
        }
    }

    /**
     * @throws \ReflectionException
     * @throws ExceptionInterface
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $class = $this->getRealClassName($data);
        $mapping = $this->fieldMapper->getMapping(new \ReflectionClass($data));

        if ($data instanceof CommentResourceInterface) {
            return $this->processComment($data, $mapping, $operation);
        }

        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($class);

        $mainObject = $this->entityManager
            ->getRepository($resourceSourceProvider->getMainClass())
            ->find($data->getMainClassId());

        [$expectedInterface, $payload] = $this->buildPayload($mainObject);

        $client = $this->clientResolver->resolve($operation);

        if (!$mainObject instanceof $expectedInterface) {
            throw new \Exception(\sprintf('Main class for %s Issue must implement %s, got %s', $client instanceof JiraTracteasyClient ? 'JiraTracteasy' : 'Jira', $expectedInterface, get_debug_type($mainObject)));
        }

        foreach ($data as $property => $value) {
            if (null === $value) {
                continue;
            }

            if (\array_key_exists($property, $mapping)) {
                foreach ($mapping[$property] as $mappingConfig) {
                    $payload = [...$payload, ...$this->transformValue($value, $mappingConfig, $data)];
                }
                continue;
            }
            $payload[$property] = $value;
        }

        $response = json_decode($client->doRequest($resourceSourceProvider->getWriteOperation(), null, ['json' => ['fields' => $payload]], Request::METHOD_POST)->getContent(), true);

        return $this->denormalizer->denormalize($response, $class, null, $context);
    }

    /**
     * @param array<string, array<int, array{transformer: array<int, string>, options: array}>> $mapping
     */
    private function processComment(CommentResourceInterface $data, array $mapping, Operation $operation): array
    {
        $payload = [];

        foreach (get_object_vars($data) as $property => $value) {
            if (null === $value) {
                continue;
            }

            if (\array_key_exists($property, $mapping)) {
                foreach ($mapping[$property] as $mappingConfig) {
                    $payload = [...$payload, ...$this->transformValue($value, $mappingConfig, $data)];
                }
                continue;
            }
            $payload[$property] = $value;
        }

        $client = $this->clientResolver->resolve($operation);

        // Jira's response contains the comment back as ADF (an array) under "body", which doesn't
        // fit JiraIssueComment's typed properties
        // As nothing consumes this response today it is deliberately returned raw instead of denormalized.
        // This avoid messenger to fail and retry 3 times, posting the whole comment each time (making duplicates comments in Jira)
        // TODO: when handling incoming comments in ADF format will be needed, a transformer AdfToHtml should be developed and this method should be updated then
        return json_decode($client->doRequest(\sprintf('issue/%s/comment', $data->getIssueKey()), null, ['json' => $payload], Request::METHOD_POST)->getContent(), true);
    }

    private function transformValue($value, array $mappingConfig, object $object): mixed
    {
        $transformers = $mappingConfig['transformer'];

        foreach ($transformers as $transformer) {
            if (!\array_key_exists($transformer, $this->transformers)) {
                continue;
            }
            $value = $this->transformers[$transformer]($value, $mappingConfig['options'], $object);
        }

        return $value;
    }

    private function buildPayload(object $mainObject): array
    {
        return match (true) {
            $mainObject instanceof IssueByKeyInterface => [IssueByKeyInterface::class, [
                'project' => ['key' => $mainObject->getProjectKey()],
            ]],
            $mainObject instanceof IssueInterface => [IssueInterface::class, [
                'project' => ['id' => $mainObject->getProjectNumber()],
            ]],
            default => [IssueInterface::class, [
                'project' => ['id' => $mainObject->getProjectNumber()],
            ]]
        };
    }
}

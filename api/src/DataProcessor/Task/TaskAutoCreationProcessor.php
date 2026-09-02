<?php

declare(strict_types=1);

namespace App\DataProcessor\Task;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Dto\Task\TaskBatchCreation;
use App\Entity\Task\Task;
use App\Factory\Task\TaskBatchCreationFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @template T
 */
readonly class TaskAutoCreationProcessor implements ProcessorInterface
{
    /**
     * @param iterable<TaskBatchCreationFactoryInterface> $taskAutoCreationFactories
     */
    public function __construct(
        private ValidatorInterface $validator,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        #[AutowireIterator(tag: 'app.task_batch_creation')] private iterable $taskAutoCreationFactories,
    ) {
    }

    /**
     * @param TaskBatchCreation $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        foreach ($this->taskAutoCreationFactories as $factory) {
            if (!$factory->supports($data->type)) {
                continue;
            }
            foreach ($data->referenceId as $referenceId) {
                if ($factory->shouldCreateTask($referenceId)) {
                    $task = $factory->create($data, $referenceId);
                    $this->validator->validate($task);
                    $taskOperation = $this->resourceMetadataCollectionFactory->create(Task::class)->getOperation('create_task');
                    $this->persistProcessor->process($task, $taskOperation, [], $context);
                }
            }
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

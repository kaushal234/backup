<?php

declare(strict_types=1);

namespace App\Factory\Task;

use App\Dto\Task\TaskBatchCreation;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.task_batch_creation')]
interface TaskBatchCreationFactoryInterface
{
    public function supports(string $type): bool;

    public function create(TaskBatchCreation $data, int $referenceId);

    public function shouldCreateTask(int $referenceId): bool;
}

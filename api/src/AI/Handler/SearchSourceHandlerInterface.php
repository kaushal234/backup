<?php

declare(strict_types=1);

namespace App\AI\Handler;

use App\AI\Dto\Result;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('search.handler')]
interface SearchSourceHandlerInterface
{
    public function supports(string $module): bool;

    /**
     * @param array{id: int|string, src: string} $data
     */
    public function handle(array $data): ?Result;
}

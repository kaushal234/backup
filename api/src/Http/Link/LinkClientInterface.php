<?php

declare(strict_types=1);

namespace App\Http\Link;

interface LinkClientInterface
{
    public function getCollection(string $class, string $key, array $variables = []): array;

    public function mutate(object $object, array $groups = [], array $extraProperties = [], array $variables = []): void;

    public function archive(object $object, array $variables = []): array;
}

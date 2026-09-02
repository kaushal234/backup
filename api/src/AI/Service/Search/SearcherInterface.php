<?php

declare(strict_types=1);

namespace App\AI\Service\Search;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\AI\Dto\SearchOutput;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'ai.searcher')]
#[FeatureDoc(path: 'ai-searcher.md')]
interface SearcherInterface
{
    public function search(string $query, int $limit = 10, bool $createLog = false): SearchOutput;

    public function supports(string $class): bool;
}

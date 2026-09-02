<?php

declare(strict_types=1);

namespace App\AI\Factory;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use Symfony\AI\Chat\ChatInterface;

#[FeatureDoc(path: 'ai-chat.md')]
interface ChatFactoryInterface
{
    public function createChat(string $iri): ChatInterface;
}

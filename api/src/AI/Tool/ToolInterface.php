<?php

declare(strict_types=1);

namespace App\AI\Tool;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[FeatureDoc(path: 'ai-chat.md')]
#[AutoconfigureTag(name: 'ai.tool')]
interface ToolInterface
{
}

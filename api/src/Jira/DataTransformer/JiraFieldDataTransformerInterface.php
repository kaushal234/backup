<?php

declare(strict_types=1);

namespace App\Jira\DataTransformer;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'jira.field_transformer')]
interface JiraFieldDataTransformerInterface
{
}

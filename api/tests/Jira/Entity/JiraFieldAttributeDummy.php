<?php

declare(strict_types=1);

namespace App\Tests\Jira\Entity;

use App\Jira\DataTransformer\ObjectToJiraId;
use App\Jira\DataTransformer\PropertyToCustomField;
use App\Jira\DataTransformer\PropertyToCustomUrlField;
use App\Jira\Mapping\Attributes\JiraField;

class JiraFieldAttributeDummy
{
    #[JiraField(transformer: ObjectToJiraId::class, options: ['field' => 'dumb'])]
    protected string $dummyPassion;

    #[JiraField(transformer: PropertyToCustomField::class, options: ['field' => 'custom'])]
    #[JiraField(transformer: PropertyToCustomUrlField::class, options: ['field' => 'custom', 'route' => 'route'])]
    protected string $dummyMoore;

    public function __construct(string $dummyPassion, string $dummyMoore)
    {
        $this->dummyPassion = $dummyPassion;
        $this->dummyMoore = $dummyMoore;
    }
}

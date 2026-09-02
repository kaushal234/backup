<?php

declare(strict_types=1);

namespace App\AI\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\AI\DataProcessor\DispatchDataProcessor;
use App\Entity\AI\AILog;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/dispatch',
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: DispatchDataProcessor::class,
        ),
    ],
    routePrefix: 'ai',
)]
class Dispatch
{
    public string $input;
    public AILog $log;
}

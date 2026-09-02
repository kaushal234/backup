<?php

declare(strict_types=1);

namespace App\Postman\Resource;

class JsonBody implements BodyInterface
{
    public string $mode = 'raw';
    public string $raw;
    public string|array $formdata;
    public array $options = [
        'raw' => [
            'language' => 'json',
        ],
    ];
}

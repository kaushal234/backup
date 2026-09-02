<?php

declare(strict_types=1);

namespace App\Jira\DataTransformer;

class TextToDocumentField implements JiraFieldDataTransformerInterface
{
    public function __invoke($value, array $options)
    {
        if (null === $value) {
            return null;
        }

        return [$options['field'] => [
            'content' => [
                [
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $value,
                        ],
                    ],
                    'type' => 'paragraph',
                ],
            ],
            'type' => 'doc',
            'version' => 1,
        ]];
    }
}

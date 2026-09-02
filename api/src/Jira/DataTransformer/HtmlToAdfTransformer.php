<?php

declare(strict_types=1);

namespace App\Jira\DataTransformer;

use App\Jira\Adf\HtmlToAdfConverter;

class HtmlToAdfTransformer implements JiraFieldDataTransformerInterface
{
    public function __construct(
        private readonly HtmlToAdfConverter $converter,
    ) {
    }

    public function __invoke($value, array $options)
    {
        if (null === $value) {
            return null;
        }

        return [$options['field'] => $this->converter->convert((string) $value)];
    }
}

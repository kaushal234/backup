<?php

declare(strict_types=1);

namespace App\Jira\DataTransformer;

use App\Jira\Enum\CustomField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PropertyToCustomUrlField implements JiraFieldDataTransformerInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke($value, array $options)
    {
        if (null === $value) {
            return [$options['field'] => null];
        }

        $key = $options['field'] instanceof CustomField ? $options['field']->value : $options['field'];

        return [$key => $this->urlGenerator->generate($options['route'], ['id' => $value])];
    }
}

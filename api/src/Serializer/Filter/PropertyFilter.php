<?php

declare(strict_types=1);

namespace App\Serializer\Filter;

use ApiPlatform\Serializer\Filter\FilterInterface;
use ApiPlatform\Serializer\Filter\PropertyFilter as BasePropertyFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

class PropertyFilter implements FilterInterface
{
    private readonly BasePropertyFilter $decorated;
    private readonly ?array $whitelist;
    private string $parameterName;

    public function __construct(string $parameterName = 'properties', bool $overrideDefaultProperties = false, ?array $whitelist = null)
    {
        $this->parameterName = $parameterName;
        $this->whitelist = $whitelist;
        $this->decorated = new BasePropertyFilter($parameterName, $overrideDefaultProperties, $whitelist);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, bool $normalization, array $attributes, array &$context): void
    {
        $this->decorated->apply($request, $normalization, $attributes, $context);

        if (isset($context['attributes']) && (bool) ($context[ContextFilter::CSV_HEADERS_ENABLED] ?? false)) {
            $context[CsvEncoder::HEADERS_KEY] = $this->flattenAttributes($context['attributes']);
            unset($context[ContextFilter::CSV_HEADERS_ENABLED]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        $description = $this->decorated->getDescription($resourceClass);
        $description[$this->parameterName] = [
            'type' => 'string',
            'required' => false,
        ];

        $properties = $this->generateNestedDescription($this->whitelist, $this->parameterName);
        foreach ($properties as $parameterName) {
            $description[$parameterName] = [
                'type' => 'string',
                'required' => false,
            ];
        }

        return $description;
    }

    protected function generateNestedDescription(array $properties, $parent): array
    {
        $flattenProperties = [];

        $properties = array_filter($properties, '\is_string', \ARRAY_FILTER_USE_KEY);

        foreach ($properties as $property => $value) {
            $fullName = '' === $parent ? $property : \sprintf('%s[%s]', $parent, $property);
            $flattenProperties[] = $fullName;
            if (\is_array($value) && ($recursion = $this->generateNestedDescription($value, $fullName))) {
                $flattenProperties = [...$flattenProperties, ...$recursion];
            }
        }

        return array_unique($flattenProperties);
    }

    private function flattenAttributes(array $attributes): array
    {
        $newAttributes = [];
        foreach ($attributes as $key => $value) {
            if (\is_int($key)) {
                if (!\is_string($value)) {
                    throw new \LogicException('This item should not be an array');
                }
                $newAttributes[] = $value;
                continue;
            }

            if (\is_array($value)) {
                foreach ($this->flattenAttributes($value) as $item) {
                    if (!\is_string($item)) {
                        throw new \LogicException('This item should be a string');
                    }

                    $newAttributes[] = $key.'.'.$item;
                }
            }
        }

        return $newAttributes;
    }
}

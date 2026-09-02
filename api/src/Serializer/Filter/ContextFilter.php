<?php

declare(strict_types=1);

namespace App\Serializer\Filter;

use ApiPlatform\Serializer\Filter\FilterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

final class ContextFilter implements FilterInterface
{
    /**
     * @var string
     */
    public const CSV_HEADERS_ENABLED = CsvEncoder::HEADERS_KEY.'_enabled';
    public const CONVERT_TO = 'convertTo';
    private readonly string $parameterName;
    private array $whitelist = [
        CsvEncoder::DELIMITER_KEY,
        DateTimeNormalizer::FORMAT_KEY,
        DateTimeNormalizer::TIMEZONE_KEY,
        self::CSV_HEADERS_ENABLED,
        CsvEncoder::NO_HEADERS_KEY,
        self::CONVERT_TO,
    ];

    public function __construct(string $parameterName = 'context')
    {
        $this->parameterName = $parameterName;
    }

    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed> $context
     */
    public function apply(Request $request, bool $normalization, array $attributes, array &$context): void
    {
        if (\array_key_exists($this->parameterName, $commonAttribute = $request->attributes->get('_api_filters', []))) {
            $additionalContext = $commonAttribute[$this->parameterName];
        } else {
            $additionalContext = $request->query->all()[$this->parameterName] ?? null;
        }

        if (!\is_array($additionalContext)) {
            return;
        }

        /** @var array<string, mixed> $additionalContext */
        if ((bool) ($additionalContext[self::CSV_HEADERS_ENABLED] ?? false)) {
            $context[self::CSV_HEADERS_ENABLED] = true;
        }

        // Do not mess with other serializer Filter
        unset($additionalContext[AbstractNormalizer::GROUPS], $additionalContext[AbstractNormalizer::ATTRIBUTES]);

        $additionalContext = array_intersect_key($additionalContext, array_flip($this->whitelist));

        $context = $additionalContext + $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach ($this->whitelist as $parameterName) {
            $description[\sprintf('%s[%s]', $this->parameterName, $parameterName)] = [
                'type' => 'string',
                'is_collection' => true,
                'required' => false,
            ];
        }

        return $description;
    }
}

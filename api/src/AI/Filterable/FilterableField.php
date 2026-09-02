<?php

declare(strict_types=1);

namespace App\AI\Filterable;

final readonly class FilterableField
{
    public const string TYPE_STRING = 'string';
    public const string TYPE_INT = 'int';
    public const string TYPE_FLOAT = 'float';
    public const string TYPE_BOOL = 'bool';
    public const string TYPE_DATE = 'date';

    /**
     * @param string        $name        Field name as exposed to the LLM (used as key in the filters payload)
     * @param string        $type        One of the TYPE_* constants
     * @param bool          $multi       True if the field accepts an array of values (OR semantic)
     * @param string[]|null $enum        Optional list of allowed values
     * @param string        $description Human-readable description for the LLM
     */
    public function __construct(
        public string $name,
        public string $type,
        public bool $multi = false,
        public ?array $enum = null,
        public string $description = '',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toSchema(): array
    {
        $schema = [
            'name' => $this->name,
            'type' => $this->type,
            'multi' => $this->multi,
            'description' => $this->description,
        ];

        if (null !== $this->enum) {
            $schema['enum'] = $this->enum;
        }

        return $schema;
    }
}

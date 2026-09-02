<?php

declare(strict_types=1);

namespace App\Report\DataProvider\Extractor;

class LabelExtractor
{
    private array $data = [];

    private readonly string $axis;

    public function __construct(array $data, string $axis)
    {
        $this->data = $data;
        $this->axis = $axis;
    }

    public function __invoke(): array
    {
        $uniqueLabels = array_unique(array_column($this->data, $this->axis));
        $uniqueLabels = array_map(fn ($value) => [$this->axis => $value], $uniqueLabels);

        usort($uniqueLabels, fn ($a, $b) => strcasecmp(str_replace('_', '', (string) $a[$this->axis]), str_replace('_', '', (string) $b[$this->axis])));

        return $uniqueLabels;
    }
}

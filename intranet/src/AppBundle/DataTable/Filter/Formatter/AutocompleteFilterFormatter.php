<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Formatter;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Exception\NotFoundValueException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;

readonly class AutocompleteFilterFormatter
{
    public function __construct(
        private Client $client,
        private ?\Closure $callback = null,
    ) {
    }

    public function __invoke(FilterData $data, FilterInterface $filter)
    {
        $multiple = $filter->getFormOptions()['form_options']['multiple'] ?? false;
        $value = $data->getValue();
        $apiValue = null;

        // Get remote data if the value is an API ressource.
        if (false === $multiple && \is_string($value) && !empty($value)) {
            $apiValue = $this->getRemoteValue($filter, $value);
        }

        // For multiple autocomplete.
        if ($multiple && \is_array($value)) {
            $values = [];
            foreach (array_filter($value) as $item) {
                $values[] = $this->getRemoteValue($filter, $item);
            }
            $apiValue = $values;
        }

        // Set default value
        if (null === $apiValue) {
            $apiValue = $value;
        }

        // If we didn't find any data, just return the first value.
        if (!\is_array($apiValue) && !$apiValue instanceof ApiData) {
            return $value;
        }

        if (\is_callable($this->callback)) {
            $callback = $this->callback;
            if ($multiple) {
                $results = array_map(static fn ($item) => $callback($item), array_filter($apiValue));
                $value = array_filter($results);
            } else {
                $value = $callback($apiValue);
            }
        }

        if ($multiple && empty($value)) {
            return null;
        }

        return $value;
    }

    private function getRemoteValue(FilterInterface $filter, string $value): array
    {
        try {
            $value = $this->client->get($value);
        } catch (\Exception $e) {
            throw new NotFoundValueException(\sprintf('Default value "%s" of filter "%s" not found, with API error: %s', $value, $filter->getFormName(), $e->getMessage()));
        }

        return $value;
    }
}

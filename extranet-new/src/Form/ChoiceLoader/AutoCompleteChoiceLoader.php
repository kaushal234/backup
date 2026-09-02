<?php

declare(strict_types=1);

namespace App\Form\ChoiceLoader;

use App\Sdk\Client;
use App\Sdk\Resource\ResourceInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class AutoCompleteChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T> $resourceClass
     */
    public function __construct(
        private readonly Client $client,
        private readonly string $resourceClass,
    ) {
    }

    public function loadChoiceList(?callable $value = null): ChoiceListInterface
    {
        return new ArrayChoiceList([], $value);
    }

    /**
     * @return array<object>
     */
    public function loadChoicesForValues(array $values, ?callable $value = null): array
    {
        $values = array_filter($values);

        if (empty($values)) {
            return [];
        }

        return $this->client->findAll($this->resourceClass, [
            'query' => [
                'id' => $values,
            ],
        ])->toArray();
    }

    /**
     * @param array<object|null> $choices
     *
     * @return array<string>
     */
    public function loadValuesForChoices(array $choices, ?callable $value = null): array
    {
        $values = [];

        foreach ($choices as $choice) {
            if (null !== $choice) {
                $values[] = (string) $choice->id;
            }
        }

        return $values;
    }
}

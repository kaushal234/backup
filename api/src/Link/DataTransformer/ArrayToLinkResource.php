<?php

declare(strict_types=1);

namespace App\Link\DataTransformer;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('link.field_transformer')]
class ArrayToLinkResource extends AbstractTransformer
{
    public function __invoke(array $array, array $options, string $field)
    {
        if (!isset($options['property'])) {
            throw new \Exception('You should set the "property" option to use the "ArrayToLinkResource" transformer.');
        }

        $value = $this->transformValue($array, $options);

        return null !== $value ? [$field => [$options['property'] => $value]] : [];
    }
}

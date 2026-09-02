<?php

declare(strict_types=1);

namespace ApiBundle\Request;

use ApiBundle\Hydra\HydraCollection;

class RequestParameterBagFilter
{
    public function filter(array $parameters, HydraCollection $collection): array
    {
        $filterableProperties = array_column($collection->metadata->get('search')['hydra:mapping'], 'variable');
        foreach ($parameters as $key => $value) {
            $originalKey = $key;
            if (\is_array($value) && array_keys($value) !== range(0, \count($value) - 1)) {
                [$key] = explode('=', urldecode(http_build_query([$key => $value])));
            }
            if (!\in_array($key, $filterableProperties, true)) {
                unset($parameters[$originalKey]);
            }
        }

        return $parameters;
    }
}

<?php

declare(strict_types=1);

namespace App\Link\DataTransformer;

use App\Link\Formatter\FormatterLinkInterface;
use App\Link\Mapping\Attributes\LinkField;

class AbstractTransformer
{
    public function transformValue(array $array, array $options)
    {
        $value = $array[$options['property']] ?? null;

        if (isset($options['class'])) {
            $reflectionProperty = new \ReflectionProperty($options['class'], $options['property']);

            foreach ($reflectionProperty->getAttributes(LinkField::class) as $attribute) {
                /** @var LinkField $linkAttribute */
                $linkAttribute = $attribute->newInstance();

                if (null === ($transformer = $linkAttribute->transformer)) {
                    continue;
                }

                if (!($formatter = new $transformer()) instanceof FormatterLinkInterface) {
                    continue;
                }

                $value = $formatter->formatValue($array[$options['property']]);
            }
        }

        return $value;
    }
}

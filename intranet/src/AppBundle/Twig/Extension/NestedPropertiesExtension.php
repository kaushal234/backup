<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Alvest\TwigHelper\Twig\Extension\NestedPropertiesExtension as AlvestNestedPropertiesExtension;
use ApiBundle\Iri\Iri;

class NestedPropertiesExtension extends AlvestNestedPropertiesExtension
{
    public function parseNested($item, $path, $forceIri = false): mixed
    {
        $nestedProperties = explode('.', $path);
        $path = \sprintf('[%s]', implode('][', $nestedProperties));

        if ('@id' === end($nestedProperties) && !$forceIri) {
            array_pop($nestedProperties);

            if ([] === $nestedProperties) {
                return Iri::id($item);
            }

            return Iri::id(parent::access($item, $path));
        }

        return parent::access($item, $path);
    }
}

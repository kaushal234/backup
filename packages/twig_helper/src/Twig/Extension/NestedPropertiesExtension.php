<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Twig\Extension;

use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use function is_array;
use function sprintf;

class NestedPropertiesExtension extends AbstractExtension
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $accessor;

    public function __construct(PropertyAccessorInterface $accessor)
    {
        $this->accessor = $accessor;
    }

    /**
     * @return array<object> An array of functions
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('parse_nested', [$this, 'parseNested']),
        ];
    }

    /**
     * @param array<string|mixed>|object $item
     * @param string                     $path
     * @param bool                       $forceIri
     *
     * @return mixed|null
     */
    public function parseNested($item, $path, $forceIri = false)
    {
        $nestedProperties = explode('.', $path);

        if ('@id' === end($nestedProperties) && !$forceIri) {
            array_pop($nestedProperties);
            $nestedProperties[] = 'id';
        }

        if (is_array($item)) {
            $path = sprintf('[%s]', implode('][', $nestedProperties));
        }

        return $this->access($item, $path);
    }

    /**
     * @param array<string|mixed>|object $item
     *
     * @return mixed|null
     */
    protected function access($item, string $path)
    {
        try {
            return $this->accessor->getValue($item, $path);
        } catch (UnexpectedTypeException $e) {
            return null;
        }
    }
}

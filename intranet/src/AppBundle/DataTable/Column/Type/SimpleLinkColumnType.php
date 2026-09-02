<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SimpleLinkColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildValueView(ColumnValueView $view, ColumnInterface $column, array $options): void
    {
        $route = $options['route'];
        $key = $options['key'];
        $extraParams = $options['extra_params'];

        $propertyPathLink = $options['property_path_link'] ?? $options['key'];
        $urlValue = $options['property_accessor']->getValue($view->parent->data, $propertyPathLink);
        if (null !== $urlValue) {
            $view->vars['href'] = $this->urlGenerator->generate($route, [$key => $urlValue] + $extraParams);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'route' => null,
                'key' => 'id',
                'extra_params' => [],
                'property_path_link' => null,
                'property_accessor' => PropertyAccess::createPropertyAccessorBuilder()
                    ->disableExceptionOnInvalidPropertyPath()
                    ->getPropertyAccessor(),
            ])
            ->setAllowedTypes('route', 'string')
            ->setAllowedTypes('key', 'string')
            ->setAllowedTypes('extra_params', 'array')
            ->setAllowedTypes('property_path_link', ['null', 'string'])
            ->setRequired('route')
        ;
    }

    public function getParent(): ?string
    {
        return LinkColumnType::class;
    }
}

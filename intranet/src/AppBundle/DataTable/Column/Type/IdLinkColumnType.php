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

class IdLinkColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildValueView(ColumnValueView $view, ColumnInterface $column, array $options): void
    {
        $route = $options['route'];
        $object = $view->parent->data;

        if ($options['routeAsCallable']) {
            $route = $route($object);
        }

        $params = null !== $options['params']
            ? $options['params']($object)
            : ['id' => $view->parent->data->getIriId()];

        $view->vars = array_replace($view->vars, [
            'href' => $this->urlGenerator->generate($route, $params),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'route' => null,
                'routeAsCallable' => false,
                'params' => null,
                'property_accessor' => PropertyAccess::createPropertyAccessorBuilder()
                    ->disableExceptionOnInvalidPropertyPath()
                    ->getPropertyAccessor(),
            ])
            ->setAllowedTypes('route', ['string', 'callable'])
            ->setRequired('route')
            ->setAllowedTypes('routeAsCallable', ['boolean'])
            ->setAllowedTypes('params', ['null', 'callable'])
        ;
    }

    public function getParent(): ?string
    {
        return LinkColumnType::class;
    }
}

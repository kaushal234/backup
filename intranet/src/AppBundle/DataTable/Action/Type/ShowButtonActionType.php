<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Action\Type;

use ApiBundle\Iri\Iri;
use Kreyu\Bundle\DataTableBundle\Action\ActionInterface;
use Kreyu\Bundle\DataTableBundle\Action\ActionView;
use Kreyu\Bundle\DataTableBundle\Action\Type\AbstractActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ShowButtonActionType extends AbstractActionType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    public function buildView(ActionView $view, ActionInterface $action, array $options): void
    {
        $route = $options['route'];

        $object = $view->parent->data;

        if (\is_callable($route)) {
            $route = $route($object);
        }

        $key = $options['key'];
        $extraParams = $options['extra_params'];

        $propertyPathLink = $options['property_path_link'] ?? $options['key'];

        if ('@id' === $propertyPathLink) {
            $urlValue = Iri::id($view->parent->data);
        } else {
            $urlValue = $this->propertyAccessor->getValue($view->parent->data, $propertyPathLink);
        }

        if (null !== $urlValue) {
            $view->vars['href'] = $this->urlGenerator->generate($route, [$key => $urlValue] + $extraParams);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => false,
                'icon' => 'bi:eye',
                'variant' => 'primary',
                'route' => null,
                'key' => 'id',
                'extra_params' => [],
                'property_path_link' => null,
            ])
            ->setAllowedTypes('route', ['string', 'callable'])
            ->setAllowedTypes('key', 'string')
            ->setAllowedTypes('extra_params', 'array')
            ->setAllowedTypes('property_path_link', ['null', 'string'])
            ->setRequired('route')
        ;
    }

    public function getParent(): ?string
    {
        return ButtonActionType::class;
    }
}

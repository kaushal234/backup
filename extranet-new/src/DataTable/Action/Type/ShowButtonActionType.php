<?php

declare(strict_types=1);

namespace App\DataTable\Action\Type;

use Kreyu\Bundle\DataTableBundle\Action\ActionInterface;
use Kreyu\Bundle\DataTableBundle\Action\ActionView;
use Kreyu\Bundle\DataTableBundle\Action\Type\AbstractActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AutoconfigureTag(name: 'kreyu_data_table.action.type')]
final class ShowButtonActionType extends AbstractActionType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(ActionView $view, ActionInterface $action, array $options): void
    {
        $route = $options['route'];
        $key = $options['key'];
        $extraParams = $options['extra_params'];

        $propertyPathLink = $options['property_path_link'] ?? $options['key'];

        $urlValue = $this->propertyAccessor->getValue($view->parent->data, $propertyPathLink);
        if (null !== $urlValue) {
            $view->vars['href'] = $this->urlGenerator->generate($route, [$key => $urlValue] + $extraParams);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => false,
                'icon_attr' => ['type' => 'tabler:eye'],
                'attr' => ['button_class' => 'btn-primary'],
                'route' => null,
                'key' => 'id',
                'extra_params' => [],
                'property_path_link' => null,
            ])
            ->setAllowedTypes('route', 'string')
            ->setAllowedTypes('key', 'string')
            ->setAllowedTypes('extra_params', 'array')
            ->setAllowedTypes('property_path_link', ['null', 'string'])
            ->setRequired('route')
        ;
    }

    public function getParent(): string
    {
        return ButtonActionType::class;
    }
}

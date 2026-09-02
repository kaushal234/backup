<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Action\Type;

use Kreyu\Bundle\DataTableBundle\Action\ActionBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Action\ActionInterface;
use Kreyu\Bundle\DataTableBundle\Action\ActionView;
use Kreyu\Bundle\DataTableBundle\Action\Type\AbstractActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TooltipButtonActionType extends AbstractActionType
{
    private const array PLACEMENT = ['top', 'bottom', 'left', 'right'];

    public function buildAction(ActionBuilderInterface $builder, array $options = []): void
    {
        if (false === \in_array($options['tooltip_placement'], self::PLACEMENT, true)) {
            throw new InvalidOptionsException('Option "placement" must be one of "top", "bottom", "left", "right".');
        }
    }

    public function buildView(ActionView $view, ActionInterface $action, array $options): void
    {
        if ($view->parent instanceof ColumnValueView && \is_callable($options['tooltip_title'])) {
            $options['tooltip_title'] = $options['tooltip_title']($view->parent->data);
        }

        $view->vars = array_replace($view->vars, [
            'tooltip_placement' => $options['tooltip_placement'],
            'tooltip_title' => strip_tags($options['tooltip_title']),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'tooltip_placement' => 'top',
                'tooltip_title' => null,
            ])
            ->setAllowedTypes('tooltip_placement', ['string'])
            ->setAllowedTypes('tooltip_title', ['string', 'callable'])
            ->isRequired('tooltip_placement')
        ;
    }

    public function getParent(): ?string
    {
        return ButtonActionType::class;
    }
}

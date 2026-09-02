<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Action\Type;

use Kreyu\Bundle\DataTableBundle\Action\ActionInterface;
use Kreyu\Bundle\DataTableBundle\Action\ActionView;
use Kreyu\Bundle\DataTableBundle\Action\Type\AbstractActionType;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateActionType extends AbstractActionType
{
    public function buildView(ActionView $view, ActionInterface $action, array $options): void
    {
        if ($view->parent instanceof ColumnValueView) {
            $view->vars['data'] = $view->parent->data;
        }

        if (\is_callable($templatePath = $options['template_path'])) {
            $templatePath = $templatePath($view->vars, $action);
        }

        if (\is_callable($templateVars = $options['template_vars'])) {
            $templateVars = $templateVars($view->vars, $action);
        }

        $view->vars = array_merge($view->vars, [
            'template_path' => $templatePath,
            'template_vars' => $templateVars,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired([
                'template_path',
            ])
            ->setDefaults([
                'template_vars' => [],
            ])
            ->setAllowedTypes('template_path', ['string', 'callable'])
            ->setAllowedTypes('template_vars', ['array', 'callable'])
            ->setInfo('template_path', 'A path to the template that should be rendered.')
            ->setInfo('template_vars', 'An array of variables passed to the template.')
        ;
    }
}

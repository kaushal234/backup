<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\StimulusBundle\Helper\StimulusHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Simple select form using TomSelect JS component.
 */
class SelectFormType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        #[Autowire(service: 'stimulus.helper')]
        private readonly StimulusHelper $stimulusHelper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Simple tags are supported by TomSelect, but tags can also be remote, with adding a tag option etc.
        // So implementation is not easy and not required at this time.
        if ($options['tags']) {
            throw new InvalidOptionsException('Tags is not supported.');
        }
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (empty($options['placeholder'])) {
            $options['placeholder'] = $this->translator->trans('make_selection');
        }

        // Let the dropdown open on select multiple
        if ($options['multiple']) {
            $options['close_on_select'] = false;
        }

        $view->vars = array_merge($view->vars, [
            'placeholder' => $options['placeholder'],
            'allowClear' => $options['allow_clear'],
            'closeOnSelect' => $options['close_on_select'],
            'maxSelectionLength' => $options['max_selection_length'],
            'redirectRoute' => $options['redirect_route'],
            'redirectRouteParamsMap' => $options['redirect_route_params_map'],
            'redirectRouteParamsExtra' => $options['redirect_route_params_extra'],
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        $attr = $this->stimulusHelper->createStimulusAttributes();

        $attr->addController('autocomplete');
        $attr->addController('symfony/ux-autocomplete/autocomplete');

        // Redirect to a route when an option is selected (values are rendered by the form theme).
        if (null !== $options['redirect_route']) {
            $attr->addController('redirect-select');
        }

        $view->vars['attr'] = $attr->toArray();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'allow_clear' => null,
                'close_on_select' => null,
                'max_selection_length' => null,
                'tags' => null,
                'redirect_route' => null,
                'redirect_route_params_map' => ['@id' => 'id'],
                'redirect_route_params_extra' => [],
            ])
            ->setAllowedTypes('allow_clear', ['null', 'boolean'])
            ->setAllowedTypes('close_on_select', ['null', 'boolean'])
            ->setAllowedTypes('max_selection_length', ['null', 'integer'])
            ->setAllowedTypes('tags', ['null', 'boolean'])
            ->setAllowedTypes('redirect_route', ['null', 'string'])
            ->setAllowedTypes('redirect_route_params_map', 'array')
            ->setAllowedTypes('redirect_route_params_extra', 'array')
            ->setInfo('allow_clear', 'When set to true, causes a clear button ("x" icon) to appear on the select box when a value is selected (Default "true").')
            ->setInfo('close_on_select', 'Controls whether the dropdown is closed after a selection is made (Default "true").')
            ->setInfo('max_selection_length', 'The maximum number of items that may be selected in a multi-select control.')
            ->setInfo('tags', 'Allow dynamically create new options from text input by the user in the search box (Default "false").')
            ->setInfo('redirect_route', 'When set, selecting an option redirects to this route name.')
            ->setInfo('redirect_route_params_map', 'Map of "<selected data property>" => "<route param name>" (Default "@id" => "id").')
            ->setInfo('redirect_route_params_extra', 'Static extra parameters merged into the redirect route.')
        ;
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}

<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use ApiBundle\Model\ApiData;
use AppBundle\Form\ChoiceList\Loader\AutoSubmittedChoiceLoader;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\StimulusBundle\Helper\StimulusHelper;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TemplateWrapper;

/**
 * Select autocomplete with remote data using TomSelect JS component.
 */
class AutocompleteChoiceType extends AbstractType
{
    private PropertyAccessor $propertyAccessor;

    public function __construct(
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator,
        #[Autowire(service: 'stimulus.helper')]
        private readonly StimulusHelper $stimulusHelper,
    ) {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (null === $options['template'] && null === $options['text_key']) {
            throw new MissingOptionsException('The required option "template" or "text_key" is missing.');
        }

        if (null !== $options['cascading_target_form'] && null === $options['cascading_to_filter']) {
            throw new MissingOptionsException('The required option "cascading_to_filter" is missing.');
        }

        // Default view transformer will trigger an "array to string conversion" warning.
        // We prefer to create the choice list manually in the buildView.
        $builder->resetViewTransformers();
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (empty($options['placeholder'])) {
            $options['placeholder'] = $this->translator->trans('home.quick_search.placeholder');
        }

        // ChoiceType always verifying if the form data is in the choice list.
        // So, before view, we have to manually insert the current value in the choice list to allow selected this data.
        // And we also have to format the default choices list using text_key or template option.
        $data = $form->getData();

        if (!empty($data)) {
            if (true === $options['multiple'] && \is_array($data)) {
                $view->vars['choices'] = array_map(fn ($item) => $this->resolveSelectedChoice($item, $options), $data);
            } else {
                $view->vars['choices'] = [$this->resolveSelectedChoice($data, $options)];
            }
        }

        $options['data_disabled'] = $this->resolveDisabledData($options['data_disabled'], $options);

        // Tranform array property path to object property path for JS implementation.
        // Todo: SDK, we can remove this if we work with object instead of array.
        $options['id_key'] = implode('.', (new PropertyPath($options['id_key']))->getElements());
        if ($options['text_key']) {
            $options['text_key'] = implode('.', (new PropertyPath($options['text_key']))->getElements());
        }

        // Close the dropdown only on autocomplete multiple because each search is potentially unique.
        if ($options['multiple']) {
            $options['close_on_select'] = true;
        }

        $view->vars = array_merge($view->vars, [
            'placeholder' => $options['placeholder'],
            'query' => $options['query'],
            'uri' => $options['uri'],
            'textKey' => $options['text_key'],
            'idKey' => $options['id_key'],
            'jsTemplateResult' => $options['js_template_result'],
            'jsTemplateSelection' => $options['js_template_selection'],
            'itemsPerPage' => $options['items_per_page'],
            'page' => $options['page'],
            'dataDisabled' => $options['data_disabled'],
            'minInputLength' => $options['min_input_length'],
            'closeOnSelect' => $options['close_on_select'],
            'cascadingTargetForm' => $options['cascading_target_form'],
            'cascadingFromProperty' => $options['cascading_from_property'],
            'cascadingToFilter' => $options['cascading_to_filter'],
            'searchParamName' => $options['search_param_name'],
            'searchWildcard' => $options['search_wildcard'],
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        $attr = $this->stimulusHelper->createStimulusAttributes();

        if (null !== $options['cascading_target_form']) {
            $attr->addController('cascading-select');
        }

        if (isset($view->vars['attr']['data-controller'])) {
            $attr->addController($view->vars['attr']['data-controller']);
        }

        $view->vars['attr'] = $attr->toArray();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'query' => [],
                'text_key' => null,
                'id_key' => '[@id]',
                'template' => null,
                'js_template_selection' => null,
                'js_template_result' => null,
                'items_per_page' => 10,
                'page' => 1,
                'data_disabled' => null,
                'min_input_length' => null,
                'cascading_target_form' => null,
                'cascading_from_property' => '@id',
                'cascading_to_filter' => null,
                'search_param_name' => 'q',
                'search_wildcard' => null,

                // We use "choice_loader" to automatically add defaults multiple value to the options list.
                // Todo: Using Symfony 7, we should probably have a look to "choice_lazy" option.
                'choice_loader' => static function (Options $options) {
                    return $options['multiple'] ? new AutoSubmittedChoiceLoader() : null;
                },
            ])
            ->setRequired('uri')
            ->setAllowedTypes('uri', 'string')
            ->setAllowedTypes('query', 'array')
            ->setAllowedTypes('text_key', ['null', 'string'])
            ->setAllowedTypes('id_key', ['null', 'string'])
            ->setAllowedTypes('template', ['null', 'string'])
            ->setAllowedTypes('js_template_selection', ['null', 'string', TemplateWrapper::class])
            ->setAllowedTypes('js_template_result', ['null', 'string', TemplateWrapper::class])
            ->setAllowedTypes('items_per_page', ['null', 'integer'])
            ->setAllowedTypes('page', ['null', 'integer'])
            ->setAllowedTypes('data_disabled', ['null', 'array'])
            ->setAllowedTypes('min_input_length', ['null', 'integer'])
            ->setAllowedTypes('cascading_target_form', ['null', 'string'])
            ->setAllowedTypes('cascading_from_property', 'string')
            ->setAllowedTypes('cascading_to_filter', ['null', 'string'])
            ->setAllowedTypes('search_param_name', ['null', 'string'])
            ->setAllowedTypes('search_wildcard', ['null', 'string'])
            ->setInfo('uri', 'Pathname of the URL ajax request.')
            ->setInfo('query', 'Add additionnal query options.')
            ->setInfo('text_key', 'Set default text key use on option text displayed.')
            ->setInfo('id_key', 'Set default ID key use on option value (Default "id").')
            ->setInfo('template', 'Customizes the way that selections and results are rendered. String, Twig format or Twig template file.')
            ->setInfo('js_template_selection', 'Customizes the way that selections are rendered. String or Handlebars format. Default use "js_template_result".')
            ->setInfo('js_template_result', 'Customizes the way that search results are rendered. String or Handlebars format. Default use "template_selection".')
            ->setInfo('items_per_page', 'Number of items per page of API result.')
            ->setInfo('page', 'Default starting page of API result.')
            ->setInfo('data_disabled', 'Set array of values to be disabled in the dropdown list.')
            ->setInfo('min_input_length', 'Minimum number of characters required to start a search.')
            ->setInfo('cascading_target_form', 'Set target form for cascading select to filter on another select depending on the result of this one.')
            ->setInfo('cascading_from_property', 'The value of the result to be used to the target select form. (Default: @id)')
            ->setInfo('cascading_to_filter', 'Filter name on the select target form.')
            ->setInfo('search_param_name', 'Name of the query parameter used to search.')
            ->setInfo('search_wildcard', 'Wildcard character used to search.')
            ->setNormalizer('template', function (Options $options, $value) {
                if (null !== $value && str_ends_with($value, '.html.twig')) {
                    return $this->loadTemplate($value, 'template');
                }

                return $value;
            })
            ->setNormalizer('js_template_selection', function (Options $options, $value) {
                // If no option js_template_selection, we use template option.
                if (!$value && $options['template']) {
                    $value = $options['template'];
                }

                if (!$value) {
                    return $value;
                }

                if (\is_string($value) && str_ends_with($value, '.html.twig')) {
                    $value = $this->loadTemplate($value, 'js_template_selection');
                }

                // We only want to send twig text to JS to be executable by Handlerbars.
                // getCode is only used for dev env or twig array loader,
                // but for perf and security, we use file_get_contents to retrieve template code.
                if ($value instanceof TemplateWrapper) {
                    if (!empty($value->getSourceContext()->getCode())) {
                        return $value->getSourceContext()->getCode();
                    }
                    if (!empty($value->getSourceContext()->getPath())) {
                        return file_get_contents($value->getSourceContext()->getPath());
                    }
                }

                return $value;
            })
            ->addNormalizer('js_template_result', function (Options $options, $value) {
                // If no option js_template_result, we use template js_template_selection.
                if (!$value && $options['js_template_selection']) {
                    $value = $options['js_template_selection'];
                }

                if (!$value) {
                    return $value;
                }

                if (\is_string($value) && str_ends_with($value, '.html.twig')) {
                    $value = $this->loadTemplate($value, 'js_template_result');
                }

                // We only want to send twig text to JS to be executable by Handlerbars.
                // getCode is only used for dev env or twig array loader,
                // but for perf and security, we use file_get_contents to retrieve template code.
                if ($value instanceof TemplateWrapper) {
                    if (!empty($value->getSourceContext()->getCode())) {
                        return $value->getSourceContext()->getCode();
                    }
                    if (!empty($value->getSourceContext()->getPath())) {
                        return file_get_contents($value->getSourceContext()->getPath());
                    }
                }

                return $value;
            })
        ;
    }

    public function getParent(): string
    {
        return SelectFormType::class;
    }

    /**
     * This method is used to create the default selected value in the choice list.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function resolveSelectedChoice(mixed $data, array $options): ChoiceView
    {
        // Ask JS part to resolve the option name.
        // This case concern a form submitted with errors; options only have IDs value.
        if (!\is_array($data) && !$data instanceof ApiData) {
            return new ChoiceView($data, $data, '', ['selected' => true, 'data-name-unresolved' => true]);
        }

        if (!$id = $this->propertyAccessor->getValue($data, $options['id_key'])) {
            throw new InvalidOptionsException(\sprintf('Impossible to access property "%s".', $options['id_key']));
        }

        $name = match (true) {
            null !== $options['text_key'] => $this->propertyAccessor->getValue($data, $options['text_key']),

            // Render Twig template file.
            $options['template'] instanceof TemplateWrapper => $this->twig->render($options['template'], $data),

            // We can directly use template format.
            null !== $options['template'] => $this->twig->render($this->twig->createTemplate($options['template']), $data),
            default => throw new UndefinedOptionsException('Option "template" or "key" is invalid.'),
        };

        return new ChoiceView($id, $id, (string) $name, ['selected' => true]);
    }

    /**
     * Resolve disabled data to only send a list of IDs to the autocomplete.
     * And it will disable these options in the UI.
     */
    private function resolveDisabledData(mixed $data, array $options): ?array
    {
        if (!$data) {
            return null;
        }

        if (!\is_array($data) && !\count($data)) {
            return null;
        }

        $result = [];
        foreach ($data as $item) {
            // Try to access to ID if we have a ApiData or a simple array.
            if ($item && $this->propertyAccessor->isReadable($item, $options['id_key'])) {
                $result[] = $this->propertyAccessor->getValue($item, $options['id_key']);

            // In case of we already have an ID.
            } elseif (!\is_array($item) && !$item) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Load a template or throw an invalid option.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function loadTemplate(string $template, string $option): TemplateWrapper
    {
        if ($this->twig->getLoader()->exists($template)) {
            return $this->twig->load($template);
        }

        throw new InvalidOptionsException(\sprintf('Twig template "%s" doesn\'t exist in "%s" option.', $template, $option));
    }
}

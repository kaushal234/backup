<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use AppBundle\Form\DataTransformer\DatePickerModelTransformer;
use DateTime;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\StimulusBundle\Helper\StimulusHelper;

class DatePickerType extends AbstractType
{
    // API format date
    public const DEFAULT_INPUT_FORMAT = "Y-m-d\TH:i:sO";

    public const string STIMULUS_CONTROLLER = 'datepicker';

    // Mapping with locales MomentJS
    protected const MOMENT_MAPPING_LOCALE = [
        'en' => 'en-us',
        'zh' => 'zh-cn',
    ];

    public function __construct(
        protected readonly TranslatorInterface $translator,
        private readonly DatePickerModelTransformer $transformer,
        #[Autowire(service: 'stimulus.helper')]
        private readonly StimulusHelper $stimulusHelper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaultFormat = $this->translator->trans('intl_format.date');

        $resolver->setDefaults([
            'input' => 'string',
            'input_format' => self::DEFAULT_INPUT_FORMAT,
            'widget' => 'single_text',
            'html5' => false,
            'locale' => 'en',
            'viewMode' => 'calendar',
            'useCurrent' => true,
            'defaultDate' => null,
            'components' => [
                'calendar' => true,
                'date' => true,
                'month' => true,
                'year' => true,
                'decades' => true,
                'clock' => false,
                'hours' => false,
                'minutes' => false,
                'seconds' => false,
            ],
            'restrictions' => [
                'minDate' => null,
                'maxDate' => null,
                'minDateStr' => null,
                'maxDateStr' => null,
            ],
            'format' => 'MM/dd/yyyy',
            'help' => 'MM/DD/YYYY', // TODO: Dynamic format in helper when Intl is back
            'js_format' => null,
            'attr' => [
                'class' => 'datepicker',
            ],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (isset(self::MOMENT_MAPPING_LOCALE[$options['locale']])) {
            $options['locale'] = self::MOMENT_MAPPING_LOCALE[$options['locale']];
        }

        $view->vars['options']['useCurrent'] = $options['useCurrent'];

        if ($options['defaultDate'] instanceof \DateTime) {
            $view->vars['options']['defaultDate'] = $options['defaultDate']->format('m/d/Y');
        }

        if (null !== $options['js_format']) {
            $view->vars['options']['format'] = $options['js_format'];
        }

        $view->vars['options']['localization'] = ['locale' => $options['locale']];
        $view->vars['options']['display'] = [
            'components' => $options['components'],
            'viewMode' => $options['viewMode'],
        ];

        // Configure minimum date with DateTime
        if (!empty($options['restrictions']['minDate'])) {
            $view->vars['options']['restrictions']['minDate'] = $options['restrictions']['minDate'];

        // Configure minimum date using string format
        } elseif (!empty($options['restrictions']['minDateStr'])) {
            $minDate = new \DateTime();
            $minInterval = \DateInterval::createFromDateString($options['restrictions']['minDateStr']);
            $minDate->add($minInterval);
            $view->vars['options']['restrictions']['minDate'] = $minDate->format('Y-m-d H:i:s');
        }

        // Configure maximum date with DateTime
        if (!empty($options['restrictions']['maxDate'])) {
            $view->vars['options']['restrictions']['maxDate'] = $options['restrictions']['maxDate'];

        // Configure maximum date using string format
        } elseif (!empty($options['restrictions']['maxDateStr'])) {
            $maxDate = new \DateTime();
            $maxInterval = \DateInterval::createFromDateString($options['restrictions']['maxDateStr']);
            $maxDate->add($maxInterval);
            $view->vars['options']['restrictions']['maxDate'] = $maxDate->format('Y-m-d H:i:s');
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        $attr = $this->stimulusHelper->createStimulusAttributes();
        $attr->addController(self::STIMULUS_CONTROLLER);
        $view->vars['attr'] = [...$view->vars['attr'], ...$attr->toArray()];
    }

    public function getParent(): string
    {
        return DateType::class;
    }
}

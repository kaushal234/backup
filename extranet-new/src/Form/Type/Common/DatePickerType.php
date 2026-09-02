<?php

declare(strict_types=1);

namespace App\Form\Type\Common;

use App\Form\DataTransformer\DatePickerModelTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class DatePickerType extends AbstractType
{
    // API format date
    public const DEFAULT_INPUT_FORMAT = "Y-m-d\TH:i:sO";

    // Mapping with locales MomentJS
    protected const MOMENT_MAPPING_LOCALE = [
        'en' => 'en-us',
        'zh' => 'zh-cn',
    ];

    public function __construct(private readonly TranslatorInterface $translator, private readonly DatePickerModelTransformer $transformer)
    {
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
            'format' => 'yyyy-MM-dd',
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
            $minDate->sub($minInterval);
            $view->vars['options']['restrictions']['minDate'] = $minDate->format('Y-m-d H:i:s');
        }

        // Configure maximum date with DateTime
        if (!empty($options['restrictions']['maxDate'])) {
            $view->vars['options']['restrictions']['maxDate'] = $options['restrictions']['maxDate'];

        // Configure maximum date using string format
        } elseif (!empty($options['restrictions']['maxDateStr'])) {
            $maxDate = new \DateTime();
            $maxInterval = \DateInterval::createFromDateString($options['restrictions']['maxDateStr']);
            $maxDate->sub($maxInterval);
            $view->vars['options']['restrictions']['maxDate'] = $maxDate->format('Y-m-d H:i:s');
        }
    }

    public function getParent(): string
    {
        return DateType::class;
    }
}

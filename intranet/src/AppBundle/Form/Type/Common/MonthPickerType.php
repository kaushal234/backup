<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonthPickerType extends DatePickerType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaultFormat = $this->translator->trans('intl_format.month');

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
                'date' => false,
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
            'format' => 'MM/yyyy',
            'js_format' => null,
            'attr' => [
                'class' => 'datepicker',
            ],
        ]);
    }

    public function getParent(): string
    {
        return DateTimeType::class;
    }
}

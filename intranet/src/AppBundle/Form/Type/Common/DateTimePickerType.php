<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimePickerType extends DatePickerType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaultFormat = $this->translator->trans('intl_format.datetime');

        $resolver->setDefaults([
            'input' => 'string',
            'input_format' => self::DEFAULT_INPUT_FORMAT,
            'widget' => 'single_text',
            'html5' => false,
            'locale' => 'en', // $this->request->getCurrentRequest()->getLocale(),
            'defaultDate' => null,
            'useCurrent' => true,
            'viewMode' => 'calendar',
            'components' => [
                'calendar' => true,
                'date' => true,
                'month' => true,
                'year' => true,
                'decades' => true,
                'clock' => true,
                'hours' => true,
                'minutes' => true,
                'seconds' => false,
            ],
            'restrictions' => [
                'minDate' => null,
                'maxDate' => null,
                'minDateStr' => null,
                'maxDateStr' => null,
            ],
            'format' => 'MM/dd/yyyy, h:mm a',
            'help' => 'MM/DD/YYYY, hh:mm AM/PM',
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

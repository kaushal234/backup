<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\SalesForecast;

use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SalesForecastEstimatedSaleDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('estimatedSaleDate', DatePickerType::class, [
                'required' => false,
                'restrictions' => [
                    'minDateStr' => '-2 years',
                    'maxDateStr' => '-5 years',
                ],
                'viewMode' => 'years',
                'format' => 'MM/yyyy',
                'useCurrent' => false,
                'defaultDate' => (new \DateTime())->modify('+2 years'),
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month',
                ],
                'label' => 'sales_forecasts.fields.estimated_sale_date',
                'help' => \sprintf('MM/YYYY  -  First available date : %s', (new \DateTime())->modify('+2 years')->format('m/Y')), // TODO: Dynamic format in helper when Intl is back
            ])
            ->add('comment', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('synchronized', CheckboxType::class, [
                'label' => 'sales_forecasts.fields.sfr_edit_linked',
                'required' => false,
            ])
            ->add('notificationRestricted', CheckboxType::class, [
                'label' => 'sales_forecasts.fields.notification_restricted',
                'required' => false,
            ])
            ->add('notifyPackage', CheckboxType::class, [
                'label' => 'sales_forecasts.fields.notify_package',
                'data' => true,
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'translation_domain' => 'messages',
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ]);

        if (!$options['showSynchronized']) {
            $builder->remove('synchronized');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_forecasts',
            'csrf_protection' => false,
            'showSynchronized' => false,
        ]);
    }
}

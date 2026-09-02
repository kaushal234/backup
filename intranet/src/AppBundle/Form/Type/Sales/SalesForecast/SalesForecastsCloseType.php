<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\SalesForecast;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesForecastsCloseType extends AbstractType
{
    final public const LOST = 'LOST';
    final public const PARTIAL = 'PARTIAL';
    final public const ORDERED = 'ORDERED';
    final public const CANCELLED = 'CANCELLED';
    final public const ORDER_CANCELLED = 'ORDER_CANCELLED';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'sales_forecasts.fields.status',
                'choices' => [
                    'Customer has placed order with competitor' => self::LOST,
                    'Part of the order was ordered, other part was lost to competitor' => self::PARTIAL,
                    'Customer has placed the order' => self::ORDERED,
                    'Requirements has been cancelled' => self::CANCELLED,
                ],
                'choice_translation_domain' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            static function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if (isset($data['status']) && self::CANCELLED === $data['status']) {
                    $form
                        ->add('status', ChoiceType::class, [
                            'label' => 'sales_forecasts.fields.status',
                            'attr' => [
                                'readonly' => true,
                            ],
                            'choices' => [
                                self::CANCELLED => self::CANCELLED,
                            ],
                            'choice_translation_domain' => false,
                        ])
                        ->add('comment', TextareaType::class, [
                            'label' => 'fields.comment',
                            'translation_domain' => 'messages',
                            'attr' => [
                                'style' => 'resize:vertical',
                            ],
                        ])
                        ->add('cancellationPropagated', CheckboxType::class, [
                            'label' => 'sales_forecasts.fields.cancel_linked_sfr',
                            'required' => false,
                        ])
                    ;
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_forecasts',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}

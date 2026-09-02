<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Position;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PositionClassificationReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            static function (FormEvent $event) {
                $form = $event->getForm();
                $data = $form->getData();
                $form
                    ->add('positionCategory', HiddenType::class)
                    ->add('correction', NumberType::class, [
                        'label' => false,
                        'html5' => false,
                        'scale' => 1,
                        'required' => false,
                        'data' => $data['correction'] ?? 0,
                    ])
                    ->add('budget', NumberType::class, [
                        'label' => false,
                        'html5' => false,
                        'scale' => 1,
                        'required' => false,
                        'data' => $data['budget'] ?? 0,
                    ])
                    ->add('reforecast', NumberType::class, [
                        'label' => false,
                        'html5' => false,
                        'scale' => 1,
                        'required' => false,
                        'data' => $data['reforecast'] ?? 0,
                    ])
                    ->add('comment', TextareaType::class, [
                        'label' => false,
                        'required' => false,
                        'empty_data' => null,
                        'attr' => [
                            'style' => 'resize:vertical',
                            'placeholder' => 'Type your comment here...',
                        ],
                    ])
                ;
            }
        );
    }
}

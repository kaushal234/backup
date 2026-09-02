<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Crab;

use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DerogationRescheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dueDate', DatePickerType::class, [
                'label' => 'fields.due_date',
                'constraints' => [
                    new NotBlank(),
                ],
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'tasks.reschedule',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'messages',
        ]);
    }
}

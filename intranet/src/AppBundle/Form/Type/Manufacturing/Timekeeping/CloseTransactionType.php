<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Manufacturing\Timekeeping;

use AppBundle\Form\Type\Common\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CloseTransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('employeeId', TextType::class, [
                'required' => true,
                'label' => 'pio.employee_id',
                'constraints' => [new Length(max: 5), new NotBlank()],
            ])
            ->add('endDate', DateTimePickerType::class, [
                'label' => 'pio.end_date',
                'required' => true,
            ])
            ->add('validate', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'pio',
        ]);
    }
}

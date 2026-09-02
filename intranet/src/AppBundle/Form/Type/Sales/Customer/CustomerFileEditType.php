<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use AppBundle\Form\Type\Directory\Division\SubDivisionChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerFileEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'customers.fields.file_description',
                'required' => true,
            ])
            ->add('subDivision', SubDivisionChoiceType::class, [
                'required' => true,
                'label' => 'directory.sub_division.name',
                'translation_domain' => 'directory',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_customers',
        ]);
    }
}

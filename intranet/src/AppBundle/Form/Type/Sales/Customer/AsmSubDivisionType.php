<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use AppBundle\Form\Type\Directory\Division\SubDivisionChoiceType;
use AppBundle\Form\Type\Directory\People\ASMAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AsmSubDivisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('asm', ASMAutocompleteChoiceType::class, [
                'label' => 'customers.fields.asm',
                'translation_domain' => 'sales_customers',
            ])
            ->add('subDivision', SubDivisionChoiceType::class, [
                'label' => 'directory.sub_division.name',
                'translation_domain' => 'directory',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
        ]);
    }
}

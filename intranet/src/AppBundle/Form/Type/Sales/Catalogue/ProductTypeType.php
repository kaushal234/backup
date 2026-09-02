<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Catalogue;

use AppBundle\Form\Type\Common\DMSAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $type = $builder->getData();

        $builder
            ->add('englishName', TextType::class, [
                'required' => true,
                'label' => 'catalogue.type.english',
            ])
            ->add('frenchName', TextType::class, [
                'required' => true,
                'label' => 'catalogue.type.french',
            ])
            ->add('spanishName', TextType::class, [
                'required' => true,
                'label' => 'catalogue.type.spanish',
            ])
            ->add('portugueseName', TextType::class, [
                'required' => true,
                'label' => 'catalogue.type.portuguese',
            ])
            ->add('russianName', TextType::class, [
                'required' => true,
                'label' => 'catalogue.type.russian',
            ])
            ->add('chineseName', TextType::class, [
                'required' => true,
                'label' => 'catalogue.type.chinese',
            ])
            ->add('germanName', TextType::class, [
                'required' => true,
                'label' => 'catalogue.type.german',
            ])
            ->add('japaneseName', TextType::class, [
                'required' => true,
                'label' => 'catalogue.type.japanese',
            ])
            ->add('dms', DMSAutocompleteChoiceType::class, [
                'label' => 'catalogue.type.dmsId',
                'translation_domain' => 'catalogue',
                'required' => false,
            ])
            ->add('publicForTLD', CheckboxType::class, [
                'required' => false,
                'label' => 'catalogue.type.public_tld',
            ])
            ->add('publicForAerospecialties', CheckboxType::class, [
                'required' => false,
                'label' => 'catalogue.type.public_aero',
            ])
            ->add('publicForSAS', CheckboxType::class, [
                'required' => false,
                'label' => 'catalogue.type.public_sas',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'catalogue.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'catalogue',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_type_form_type';
    }
}

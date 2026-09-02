<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Catalogue;

use AppBundle\Form\Type\Common\DMSAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CatalogueDMSType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dmsType', ChoiceType::class, [
                'choice_translation_domain' => false,
                'choices' => [
                    'Specs' => 'Specs',
                    'Datasheet' => 'Datasheet',
                    'Photos' => 'Photos',
                    'Presentation' => 'Presentation',
                    'Configurator' => 'Configurator',
                    'Line Drawing' => 'Line Drawing',
                    'Fact Sheet' => 'Fact Sheet',
                ],
                'label' => 'catalogue.dms.type',
                'required' => false,
            ])
            ->add('dms', DMSAutocompleteChoiceType::class, [
                'label' => 'catalogue.type.dmsId',
                'translation_domain' => 'catalogue',
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
        return 'app_catalogue_dms_form_type';
    }
}

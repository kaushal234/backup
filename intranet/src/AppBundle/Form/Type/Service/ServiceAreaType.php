<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service;

use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'service_area.fields.service_area_name',
                'translation_domain' => 'service',
            ])
            ->add('representative', PeopleAutocompleteChoiceType::class, [
                'label' => 'supplier_corrective_action_request.fields.representative',
                'translation_domain' => 'supplier_corrective_action_request',
            ])
            ->add('submit', SubmitType::class, [
                'translation_domain' => 'messages',
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'service',
            'allow_extra_fields' => true,
        ]);
    }
}

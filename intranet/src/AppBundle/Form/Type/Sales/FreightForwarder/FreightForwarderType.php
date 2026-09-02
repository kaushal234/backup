<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\FreightForwarder;

use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FreightForwarderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $freightForwarder = $builder->getData();
        $builder
            ->add('name', TextType::class, [
                'label' => 'fields.name',
                'required' => true,
            ])
            ->add('location', FactoryChoiceType::class, [
                'required' => false,
                'label' => 'fields.factory',
            ])

            ->add('supplierNumber', TextType::class, [
                'label' => 'fields.supplier.number',
                'required' => false,
            ])
            ->add('language', ChoiceType::class, [
                'choices' => [
                    '' => '',
                    'en' => 'en',
                    'fr' => 'fr',
                    'zh' => 'zh',
                ],
                'translation_domain' => 'contacts',
                'label' => 'contacts.fields.language',
                'required' => true,
            ])
            ->add('emails', CollectionType::class, [
                'entry_type' => EmailType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'label' => 'freight_forwarder.felds.emails',
                'data' => $options['add'] ? [0 => []] : $freightForwarder['emails'],
                'translation_domain' => 'freight_forwarder',
                'required' => true,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'demo.submit',
                'translation_domain' => 'demo',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'add' => false,
        ]);
    }
}

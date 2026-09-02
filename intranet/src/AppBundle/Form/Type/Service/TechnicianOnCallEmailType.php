<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service;

use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechnicianOnCallEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'contacts.mail.subject',
                'required' => true,
            ])
            ->add('message', CKEditorType::class, [
                'label' => 'contacts.mail.optional_message',
                'required' => false,
                'config_name' => 'simple',
            ])
            ->add('to', PeopleAutocompleteChoiceType::class, [
                'label' => 'contacts.mail.to',
                'required' => true,
            ])
            ->add('ccs', PeopleAutocompleteChoiceType::class, [
                'label' => 'contacts.mail.cc',
                'required' => false,
                'multiple' => true,
            ])
            ->add('bccs', PeopleAutocompleteChoiceType::class, [
                'label' => 'contacts.mail.bcc',
                'required' => false,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-info']])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'contacts',
        ]);
    }
}

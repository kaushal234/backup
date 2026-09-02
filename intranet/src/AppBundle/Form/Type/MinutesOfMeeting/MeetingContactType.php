<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\MinutesOfMeeting;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'directory.people.fields.firstname',
                'required' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'directory.people.fields.lastname',
                'required' => true,
            ])
            ->add('mail', EmailType::class, [
                'label' => 'directory.people.fields.email',
                'required' => false,
            ])
            ->add('company', TextType::class, [
                'label' => 'directory.location.fields.company',
                'required' => true,
            ])
            ->add('phone', TextType::class, [
                'label' => 'directory.location_contact.fields.telephone',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
        ]);
    }
}

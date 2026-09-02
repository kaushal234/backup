<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\MinutesOfMeeting;

use AppBundle\Form\Type\Common\DateTimePickerType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingDuplicateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', CKEditorType::class, [
                'required' => true,
                'label' => 'meeting.fields.description',
                'attr' => [
                    'style' => 'resize:vertical; height:200px',
                ],
            ])
            ->add('location', TextType::class, [
                'required' => true,
                'label' => 'menu.location.title',
                'translation_domain' => 'messages',
            ])
            ->add('meetingDate', DateTimePickerType::class, [
                'required' => true,
                'label' => 'meeting.fields.meetingDate',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'catalogue.submit',
                'translation_domain' => 'catalogue',
                'attr' => ['class' => 'btn btn-info'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'meeting',
            'csrf_protection' => false,
            'quick' => true,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis;

use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;

class AuthorizedApplicationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'mis.authorized_applications.fields.name',
            ])
            ->add('keyExpiresOn', DatePickerType::class, [
                'label' => 'mis.authorized_applications.fields.key_expires_on',
                'constraints' => [new GreaterThan(['value' => date('Y-m-d')])],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'mis',
        ]);
    }
}

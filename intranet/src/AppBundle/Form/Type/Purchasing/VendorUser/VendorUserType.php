<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\VendorUser;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'disabled' => true,
                'required' => false,
                'label' => 'settings.profile.form.firstname.label',
            ])
            ->add('lastname', TextType::class, [
                'disabled' => true,
                'required' => false,
                'label' => 'settings.profile.form.lastname.label',
            ])
            ->add('email', TextType::class, [
                'disabled' => true,
                'required' => false,
                'label' => 'settings.profile.form.email.label',
            ])
            ->add('erpIdentifier', TextType::class, [
                'required' => true,
                'label' => 'fields.erp_identifier',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
        ]);
    }
}

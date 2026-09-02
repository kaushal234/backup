<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Printer;

use AppBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrinterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'required' => true,
                'label' => 'directory.location.fields.company',
            ])
            ->add('firstname', TextType::class, [
                'required' => false,
                'label' => 'directory.people.fields.firstname',
            ])
            ->add('lastname', TextType::class, [
                'required' => false,
                'label' => 'directory.people.fields.lastname',
            ])
            ->add('email', TextType::class, [
                'required' => true,
                'label' => 'directory.people.fields.email',
            ])
            ->add('address', AddressType::class, [
                'label' => 'address.address',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
                'translation_domain' => 'messages',
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

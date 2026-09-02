<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ExtranetUser;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtranetUserEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'contacts.mail.subject',
            ])
            ->add('message', TextareaType::class, [
                'label' => 'contacts.mail.optional_message',
                'required' => false,
                'attr' => [
                    'style' => 'resize:vertical',
                ],
            ])
            ->add('ccs', ExtranetUserRepresentativesChoiceType::class, [
                'label' => 'contacts.mail.cc',
                'required' => false,
                'multiple' => true,
                'filters' => ['extranetUser' => $options['extranet_user']],
            ])
            ->add('bccs', ExtranetUserRepresentativesChoiceType::class, [
                'label' => 'contacts.mail.bcc',
                'required' => false,
                'multiple' => true,
                'filters' => ['extranetUser' => $options['extranet_user']],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'contacts',
            'extranet_user' => '',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_sales_contact_email';
    }
}

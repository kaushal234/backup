<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\Common\SwitchType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtranetUserFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', CustomerAutocompleteChoiceType::class, [
                'required' => false,
                'label' => 'contacts.fields.customer',
                'property_path' => '[extranetUserProfile.customer]',
            ])
            ->add('airport', AirportChoiceType::class, [
                'required' => false,
                'label' => 'fields.airport',
                'translation_domain' => 'messages',
                'property_path' => '[extranetUserProfile.airport]',
            ])
            ->add('erpLocation', SSOChoiceType::class, [
                'label' => 'contacts.fields.erp_location',
                'property_path' => '[extranetUserProfile.erpLocation]',
                'required' => false,
            ])
            ->add('archived', SwitchType::class, [
                'label' => 'contacts.fields.archived_accounts',
                'property_path' => '[extranetUserProfile.archived]',
                'required' => false,
            ])
            ->add('download', SubmitType::class, [
                'label' => 'menu.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-uppercase'],
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
            'csrf_protection' => false,
        ]);
    }
}

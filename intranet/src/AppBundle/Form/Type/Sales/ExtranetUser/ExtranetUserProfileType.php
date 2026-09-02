<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ExtranetUser;

use AppBundle\Form\Type\AddressType;
use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Sales\Customer\ApprovedCustomerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtranetUserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('division', TextType::class, [
                'label' => 'contacts.fields.division',
                'required' => true,
            ])
            ->add('department', TextType::class, [
                'label' => 'contacts.fields.department',
                'required' => true,
            ])
            ->add('jobTitle', TextType::class, [
                'label' => 'contacts.fields.job_title',
                'required' => true,
            ])
            ->add('language', ChoiceType::class, [
                'choices' => [
                    'en' => 'en',
                    'fr' => 'fr',
                    'es' => 'es',
                    'ru' => 'ru',
                    'zh' => 'zh',
                    'ja' => 'ja',
                ],
                'label' => 'contacts.fields.language',
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'PUNCHOUT' => 'PUNCHOUT',
                    'CART' => 'CART',
                ],
                'label' => 'contacts.fields.type',
                'required' => false,
            ])
            ->add('customerCarrierName', TextType::class, [
                'label' => 'contacts.fields.carrier_name',
                'required' => false,
            ])
            ->add('companyName', TextType::class, [
                'label' => 'contacts.fields.company',
                'required' => false,
            ])
            ->add('note', TextareaType::class, [
                'label' => 'contacts.fields.note',
                'required' => false,
            ])
            ->add('shippingAccountNumber', TextType::class, [
                'label' => 'contacts.fields.shipping_account',
                'required' => false,
                'attr' => [
                    'placeholder' => '#',
                ],
            ])
            ->add('requestorNumber', TextType::class, [
                'label' => 'contacts.fields.requestor',
                'required' => false,
                'attr' => [
                    'placeholder' => '#',
                ],
            ])
            ->add('employeeNumber', TextType::class, [
                'label' => 'contacts.fields.employee',
                'required' => false,
                'attr' => [
                    'placeholder' => '#',
                ],
            ])
            ->add('archived', CheckboxType::class, [
                'label' => 'contacts.fields.archived',
                'required' => false,
            ])
            ->add('shippingAddress', AddressType::class, [
                'label' => 'address.shipping_address',
                'required' => false,
            ])
            ->add('country', CountryChoiceType::class, [
                'label' => 'contacts.fields.country',
                'required' => true,
                'placeholder' => 'contacts.make_selection',
            ])
            ->add('customer', ApprovedCustomerAutocompleteChoiceType::class, [
                'label' => 'contacts.fields.customer',
                'placeholder' => 'contacts.make_selection',
                'required' => true,
            ])
            ->add('erpLocation', SSOChoiceType::class, [
                'label' => 'contacts.fields.erp_location',
                'required' => false,
            ])
            ->add('isGiftAccepted', CheckboxType::class, [
                'label' => 'contacts.fields.is_gift_accepted',
                'required' => false,
                'attr' => [
                    'checked' => true,
                ],
            ])
            ->add('giftRefusedReason', TextType::class, [
                'label' => 'contacts.fields.gift_refused_reason',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, static function ($event) use ($options) {
            $builder = $event->getForm();
            $extranetUserProfile = $event->getData();

            $builder->add('airport', AirportChoiceType::class, [
                'label' => 'fields.airport',
                'translation_domain' => 'messages',
                'required' => false,
            ]);

            if ($options['addProfile'] || !$extranetUserProfile['archived']) {
                $builder->remove('archived');
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'contacts',
            'addProfile' => false,
        ]);
    }
}

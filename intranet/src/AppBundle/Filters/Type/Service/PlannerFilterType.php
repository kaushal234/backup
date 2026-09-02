<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Service;

use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Service\CustomerServiceRecord\TechniciansType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlannerFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('airport', AirportChoiceType::class, [
                'required' => false,
                'label' => 'csr.fields.by_airport',
            ])
            ->add('equipmentRecord', EquipmentRecordAutocompleteChoiceType::class, [
                'label' => 'crab.fields.equipment_record',
                'translation_domain' => 'crab',
                'required' => false,
            ])
            ->add('discriminator', ChoiceType::class, [
                'required' => false,
                'label' => 'csr.fields.type',
                'choices' => CustomerServiceRecordFilterType::CUSTOMER_SERVICE_RECORD_TYPE,
            ])
            ->add('salesOrganisationService', SSOChoiceType::class, [
                'label' => 'er.fields.ssoService',
                'property_path' => '[equipmentRecord.salesOrganisationService]',
                'required' => false,
            ])
            ->add('salesOrganisation', SSOChoiceType::class, [
                'label' => 'er.fields.sso',
                'property_path' => '[equipmentRecord.salesOrganisation]',
                'required' => false,
            ])
            ->add('deliveredCountry', CountryChoiceType::class, [
                'label' => 'er.fields.delivered_country',
                'property_path' => '[equipmentRecord.deliveredCountry]',
                'required' => false,
            ])
            ->add('technicians', TechniciansType::class, [
                'property_path' => '[interventions.leader]',
            ])
        ;

        $builder->get('technicians')->addModelTransformer(new CallbackTransformer(
            static function ($value) {
                return $value;
            },
            static function ($value) {
                if (!\array_key_exists('interventions.leader', $value)) {
                    return $value;
                }

                return $value['interventions.leader'];
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'customer_service_record',
        ]);
    }
}

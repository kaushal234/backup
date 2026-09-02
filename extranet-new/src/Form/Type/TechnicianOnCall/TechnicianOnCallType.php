<?php

declare(strict_types=1);

namespace App\Form\Type\TechnicianOnCall;

use App\DataTransferObject\TechnicianOnCall\Airport as DtoAirport;
use App\DataTransferObject\TechnicianOnCall\CreateTechnicianOnCall;
use App\DataTransferObject\TechnicianOnCall\Equipment;
use App\Form\Type\Airport\AirportAutocompleteType;
use App\Form\Type\EquipmentRecord\EquipmentRecordAutocompleteType;
use App\Sdk\Client;
use App\Sdk\Resource\Airport;
use App\Sdk\Resource\EquipmentRecord;
use App\Sdk\Utils\IriToId;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class TechnicianOnCallType extends AbstractType
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('equipment', EquipmentRecordAutocompleteType::class, [
                'required' => true,
                'label' => 'extranet.fields.serial',
                'help' => 'extranet.form.help.equipment',
            ])
            ->add('airport', AirportAutocompleteType::class, [
                'required' => true,
                'label' => 'extranet.fields.airport',
                'help' => 'extranet.form.help.airport',
            ])
            ->add('originalTitle', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'extranet.form.toc.title',
                'constraints' => [new Length(min: 12)],
            ])
            ->add('originalDescription', TextareaType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'extranet.form.toc.description',
                'constraints' => [new Length(min: 12)],
            ])
            ->add('errorCodes', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'help' => 'extranet.form.help.error_codes',
                'required' => false,
            ])
            ->add('hourMeter', IntegerType::class, [
                'attr' => ['class' => 'form-control'],
                'help' => 'extranet.form.help.hourmeter',
                'data' => null,
            ])
            ->add('serviceActivity', ServiceActivityChoiceType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'extranet.fields.request_category',
                'help' => 'extranet.form.help.request_category',
            ])
            ->add('unitOperationalStatus', UnitOperationalStatusChoiceType::class, [
                'attr' => ['class' => 'form-control'],
                'help' => 'extranet.form.help.unit_operation_status',
            ])
        ;

        $client = $this->client;

        $builder->get('equipment')->addModelTransformer(new CallbackTransformer(
            static function ($value) use ($client) {
                if ($value instanceof Equipment) {
                    return $client->find(EquipmentRecord::class, ['resource_id' => IriToId::iriToId($value->iri)]);
                }

                return $value;
            },
            static function ($equipmentRecord): ?Equipment {
                if (!$equipmentRecord instanceof EquipmentRecord) {
                    return $equipmentRecord;
                }

                $equipment = new Equipment();
                $equipment->iri = $equipmentRecord->iri;

                return $equipment;
            }
        ));

        $builder->get('airport')->addModelTransformer(new CallbackTransformer(
            static function ($value) use ($client) {
                if ($value instanceof DtoAirport) {
                    return $client->find(Airport::class, ['resource_id' => IriToId::iriToId($value->iri)]);
                }

                return $value;
            },
            static function ($airport): ?DtoAirport {
                if (!$airport instanceof Airport) {
                    return $airport;
                }

                $dtoAirport = new DtoAirport();
                $dtoAirport->iri = $airport->iri;

                return $dtoAirport;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateTechnicianOnCall::class,
            'csrf_protection' => true,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\DataTransferObject\TechnicianOnCall\Airport as DtoAirport;
use App\DataTransferObject\UpdateEquipmentRecord;
use App\Form\Type\Airport\AirportAutocompleteType;
use App\Sdk\Client;
use App\Sdk\Resource\Airport;
use App\Sdk\Utils\IriToId;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EquipmentType extends AbstractType
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('iri', HiddenType::class)
            ->add('customerSerialNumber', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('airport', AirportAutocompleteType::class)
        ;

        $client = $this->client;
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
            'data_class' => UpdateEquipmentRecord::class,
            'csrf_protection' => true,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class QHSEProgressType extends AbstractType
{
    private readonly DataProvider $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $collection = $this->dataProvider->findAll('locations');
        $choices = [];
        foreach ($collection as $location) {
            if (!empty($location['capability']['factory']) || !empty($location['capability']['sparePartsHub'])) {
                $value = $location['name'];
                $choices[$value] = $location['@id'];
            }
        }

        ksort($choices);

        $builder
            ->add('location', LocationChoiceType::class, [
                'label' => 'cleanliness.fields.location',
                'choices' => $choices,
            ])
            ->add('date', DatePickerType::class, [
                'label' => 'cleanliness.fields.date',
            ])
            ->add('rating', IntegerType::class, [
                'label' => 'qhse.fields.rating',
                'translation_domain' => 'qhse',
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 100,
                        'invalidMessage' => 'cleanliness.validation_errors.invalidRatingMessage',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'cleanliness',
        ]);
    }
}

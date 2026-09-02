<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Controller\Parts\SparePartsRequestController;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class SparePartsRequestChoiceType extends AbstractType
{
    private readonly DataProvider $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new IrisResourceToIdTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'key' => '@id',
                'filters' => [],
                'sparePartsRequest' => null,
                'choice_translation_domain' => false,
                'choices' => function (Options $options) {
                    switch ($options['sparePartsRequest']['@type'] ?? null) {
                        case 'TocSparePartsRequest':
                            $collection = $this->dataProvider->findAll(
                                SparePartsRequestController::TOC_SPARE_PARTS_REQUESTS_URL,
                                $options['filters'] + ['technicianOnCall' => $options['sparePartsRequest']['technicianOnCall']['@id']],
                                ['id']
                            );
                            break;
                        case 'SbSparePartsRequest':
                            $collection = $this->dataProvider->findAll(
                                SparePartsRequestController::SB_SPARE_PARTS_REQUESTS_URL,
                                $options['filters'] + ['sbId' => $options['sparePartsRequest']['sbId']],
                                ['id']
                            );
                            break;
                        default:
                            return [];
                    }

                    $choices = [];
                    foreach ($collection as $sparePartsRequest) {
                        if ($sparePartsRequest['@id'] === $options['sparePartsRequest']['@id']) {
                            continue;
                        }
                        $value = \sprintf('SPR#%d SPH %s - %s (%s)',
                            $sparePartsRequest['id'],
                            $sparePartsRequest['sph']['name'],
                            $sparePartsRequest['airport']['code'],
                            $sparePartsRequest['airport']['cityName']
                        );
                        $choices[$value] = $sparePartsRequest[$options['key']];
                    }

                    return $choices;
                },
                'placeholder' => '',
            ]
        )->addNormalizer('filters', static fn (Options $options, $value) => $value + ['status' => ['PENDING', 'OPEN']]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return SelectFormType::class;
    }
}

<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\HumanResources;

use ApiBundle\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeStaffingSnapshotChoiceType extends AbstractType
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_translation_domain' => false,
            'entity' => null,
            'placeholder' => 'employee_staffing.fields.current',
            'translation_domain' => 'employee_staffing',
            'choices' => function (Options $options): array {
                $availableSnapshots = $this->client->get(
                    '/reports/resource=/reports/id;x=businessUnit;y=createdAt?options[resource]=/people&options[x]=businessUnit.positionClassifications.positionCategory.name&options[y]=contractType.name',
                    [
                        'query' => [
                            'options' => [
                                'resource' => '/people',
                                'x' => 'businessUnit.positionClassifications.positionCategory.name',
                                'y' => 'contractType.name',
                            ] + (null !== $options['entity'] ? ['entity' => $options['entity']] : []),
                        ],
                    ]
                );

                $dates = array_keys($availableSnapshots['yTotals']);
                rsort($dates);

                return array_combine($dates, $dates);
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}

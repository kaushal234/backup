<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\CustomerRelationshipTeam;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class CustomerRelationshipTeamChoiceType extends AbstractType
{
    private readonly DataProvider $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new IrisResourceToIdTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'key' => '@id',
            'filters' => [],
            'choices' => function (Options $options) {
                $collection = $this->dataProvider->findAll(
                    'sales/customer_relationship_teams',
                    $options['filters']
                );

                $choices = [];
                foreach ($collection as $crt) {
                    $value = \sprintf('CRT #%d - CUNO : %s - ERP : %s - ASM : %s %s',
                        $crt['id'],
                        $crt['cuno'],
                        $crt['erpLocation']['erp'],
                        $crt['salesRepresentative']['lastname'] ?? null,
                        $crt['salesRepresentative']['firstname'] ?? null,
                    );
                    $choices[$value] = $crt[$options['key']];
                }

                return $choices;
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_sales_crt_choice';
    }
}

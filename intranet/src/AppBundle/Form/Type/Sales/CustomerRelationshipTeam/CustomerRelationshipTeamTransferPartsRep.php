<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\CustomerRelationshipTeam;

use AppBundle\Form\Type\Directory\People\PartsRepresentativeAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerRelationshipTeamTransferPartsRep extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('representative', PartsRepresentativeAutocompleteChoiceType::class, [
                'label' => false,
                'required' => true,
                'placeholder' => 'customer_relationship_team.forms.make_selection',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'customer_relationship_team',
            'displayed' => false,
        ]);
    }

    public function getName(): string
    {
        return 'app_customer_relationship_team_parts_transfer';
    }
}

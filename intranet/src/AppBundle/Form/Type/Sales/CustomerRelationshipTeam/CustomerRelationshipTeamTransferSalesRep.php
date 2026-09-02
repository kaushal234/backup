<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\CustomerRelationshipTeam;

use AppBundle\Form\Type\Directory\People\ASMChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerRelationshipTeamTransferSalesRep extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('representative', ASMChoiceType::class, [
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
        ]);
    }

    public function getName(): string
    {
        return 'app_customer_relationship_team_sales_transfer';
    }
}

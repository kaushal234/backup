<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use ApiBundle\Client;
use AppBundle\Form\Type\Directory\Location\ERPLocationChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DashboardFilterType extends AbstractType
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->client->get('/me');

        $defaultLocation = $user['erp'] ?? null;

        $builder
            ->add('part_number', TextType::class, [
                'label' => 'fields.part_number',
                'translation_domain' => 'messages',
            ])
            ->add('location', ERPLocationChoiceType::class, [
                'label' => 'directory.business_unit.fields.location',
                'translation_domain' => 'directory',
                'placeholder' => 'directory.business_unit.make_selection',
                'key' => 'erp',
                'required' => false,
                'data' => $defaultLocation,
            ])
            ->add('submit_search', SubmitType::class, [
                'label' => 'contacts.search',
                'translation_domain' => 'contacts',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;

        if ($options['full_view']) {
            $builder->add('submit_show_shipments', SubmitType::class, [
                'label' => 'parts.dashboard.button.show_shipments',
                'attr' => [
                    'class' => 'btn btn-secondary',
                    'formtarget' => '_blank',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'parts',
            'full_view' => true,
        ]);
    }
}

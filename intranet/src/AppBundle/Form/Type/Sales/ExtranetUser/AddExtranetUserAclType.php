<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ExtranetUser;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use AppBundle\Form\Type\Sales\Customer\ApprovedCustomerChoiceType;
use AppBundle\Form\Type\Sales\CustomerRelationshipTeam\CustomerRelationshipTeamChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddExtranetUserAclType extends AbstractType
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', ApprovedCustomerChoiceType::class, [
                'label' => 'contacts.fields.customer',
                'required' => true,
                'show_only_with_crt' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if (isset($data['customer']) && null !== $data['customer']) {
                    $form
                        ->add('crt', CustomerRelationshipTeamChoiceType::class, [
                            'label' => 'contacts.fields.crt',
                            'required' => true,
                            'filters' => [
                                'customer' => $data['customer'],
                            ],
                        ]);
                    if (isset($data['crt']) && null !== $data['crt']) {
                        $crt = $this->client->find('sales/customer_relationship_teams', Iri::id($data['crt']));
                        $form
                            ->add('role', ExtranetUserGroupChoiceType::class, [
                                'label' => 'contacts.fields.roles',
                                'required' => true,
                                'expanded' => false,
                                'multiple' => true,
                            ])
                        ;
                    }
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'contacts',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_sales_add_extranet_user_acls';
    }
}

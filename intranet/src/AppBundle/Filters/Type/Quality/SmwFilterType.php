<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Quality;

use ApiBundle\Client;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SmwFilterType extends AbstractType
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->client->get('/me');

        $defaultLocation = $user['businessUnit']['location']['@id'] ?? null;

        if ('sso' === $options['type']) {
            $builder
                ->add('location', SSOChoiceType::class, [
                    'label' => 'quality_smw.filter.location',
                    'required' => false,
                    'data' => $defaultLocation,
                    'empty_data' => $defaultLocation,
                    'translation_domain' => 'quality_smw',
                ])
            ;
        } else {
            $builder
                ->add('location', FactoryChoiceType::class, [
                    'label' => 'quality_smw.filter.location',
                    'required' => false,
                    'data' => $defaultLocation,
                    'empty_data' => $defaultLocation,
                    'translation_domain' => 'quality_smw',
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'quality_smw',
            'csrf_protection' => false,
            'type' => 'factory',
        ]);
    }

    public function getName(): string
    {
        return 'app_smw_meeting_filter';
    }
}

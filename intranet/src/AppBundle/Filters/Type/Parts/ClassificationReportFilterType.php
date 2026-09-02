<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Parts;

use ApiBundle\Client;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\SparePartsHubChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClassificationReportFilterType extends AbstractType
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
        $defaultErp = $user['erp'] ?? null;

        $builder
            ->add('erp', SparePartsHubChoiceType::class, [
                'placeholder' => 'make_selection',
                'translation_domain' => 'messages',
                'required' => true,
                'key' => 'erp',
                'filters' => ['erpInLN' => true],
                'data' => $defaultErp,
                'empty_data' => $defaultErp,
            ])
            ->add('invoicedFrom', DatePickerType::class, [
                'label' => 'parts.classification.fields.invoiced_from',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('invoicedUntil', DatePickerType::class, [
                'label' => 'parts.classification.fields.invoiced_until',
                'widget' => 'single_text',
                'required' => false,
            ])
        ;

        if (!\in_array($defaultErp, $builder->get('erp')->getOption('choices'), true)) {
            $builder->get('erp')->setData(null);
            $builder->get('erp')->setEmptyData(null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'parts',
            'csrf_protection' => false,
            'method' => 'GET',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_shortage_filters';
    }
}

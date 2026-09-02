<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class ApprovedCustomerChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->addNormalizer('filters', static fn (Options $options, $value) => array_merge($value, ['status' => ['APPROVED', 'PENDING RE-APPROVAL']]));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return CustomerChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'app_sales_approved_customers_choice';
    }
}

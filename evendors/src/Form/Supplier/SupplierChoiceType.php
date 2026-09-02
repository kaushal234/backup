<?php

declare(strict_types=1);

namespace App\Form\Supplier;

use App\CQRS\Query\Supplier\FindAllSuppliersQuery;
use App\CQRS\QueryBusInterface;
use App\Sdk\Resource\Supplier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierChoiceType extends AbstractType
{
    public function __construct(
        private readonly QueryBusInterface $messageBus,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'key' => 'code',
                'filters' => [],
                'choice_translation_domain' => false,
                'choices' => function (Options $options) {
                    $collection = $this->messageBus->dispatch(new FindAllSuppliersQuery());

                    $choices = [];
                    /** @var Supplier $supplier */
                    foreach ($collection as $supplier) {
                        $choices[$supplier->name] = $supplier->{$options['key']};
                    }

                    return ['' => ''] + $choices;
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}

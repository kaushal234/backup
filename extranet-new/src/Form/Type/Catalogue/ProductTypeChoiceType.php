<?php

declare(strict_types=1);

namespace App\Form\Type\Catalogue;

use App\CQRS\Query\Catalogue\FindAllProductTypesQuery;
use App\CQRS\QueryBusInterface;
use App\Sdk\Resource\ProductType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductTypeChoiceType extends AbstractType
{
    public function __construct(
        private readonly QueryBusInterface $queryBus
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choice_translation_domain' => false,
                'choices' => function (Options $options) {
                    /* @var ProductType[] $productTypes */
                    return $this->queryBus->dispatch(new FindAllProductTypesQuery());
                },
                'choice_value' => static fn (?ProductType $productType) => $productType?->getIri(),
                'choice_label' => static fn (ProductType $productType) => $productType->englishName,
                'placeholder' => '',
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

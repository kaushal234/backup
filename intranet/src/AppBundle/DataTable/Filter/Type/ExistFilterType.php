<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type;

use AppBundle\DataTable\Filter\Handler\ApiExistFilterHandler;
use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExistFilterType extends AbstractApiFilterType
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder->setHandler(new ApiExistFilterHandler());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => ChoiceType::class,
                'active_filter_formatter' => static function (FilterData $data) {
                    return $data->getValue() ? 'Yes' : 'No';
                },
            ])
            ->addNormalizer('form_options', static function (Options $options, array $value): array {
                if (ChoiceType::class !== $options['form_type']) {
                    return $value;
                }

                return $value + [
                    'choices' => ['Yes' => true, 'No' => false],
                ];
            })
        ;
    }
}

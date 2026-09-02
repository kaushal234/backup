<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use App\CQRS\Query\TechnicianOnCall\FindAllServiceActivityQuery;
use App\CQRS\QueryBusInterface;
use App\Form\Type\TechnicianOnCall\ServiceActivityChoiceType;
use App\Sdk\Resource\ServiceActivity;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceActivityFilterType extends AbstractApiFilterType
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => ServiceActivityChoiceType::class,
                'active_filter_formatter' => function (FilterData $filterData): ?string {
                    $value = $filterData->getValue();

                    if (null === $value || [] === $value) {
                        return null;
                    }

                    /** @var ServiceActivity[] $serviceActivities */
                    $serviceActivities = $this->queryBus->dispatch(new FindAllServiceActivityQuery());

                    $namesByIri = [];
                    foreach ($serviceActivities as $serviceActivity) {
                        $namesByIri[$serviceActivity->iri] = $serviceActivity->name;
                    }

                    $iris = \is_array($value) ? $value : [$value];

                    return implode(', ', array_map(static fn (string $iri): string => $namesByIri[$iri] ?? $iri, $iris));
                },
            ])
        ;
    }
}

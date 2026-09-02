<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use App\CQRS\Query\TechnicianOnCall\FindAllUnitOperationalStatusQuery;
use App\CQRS\QueryBusInterface;
use App\Form\Type\TechnicianOnCall\UnitOperationalStatusChoiceType;
use App\Sdk\Resource\UnitOperationalStatus;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UnitOperationalStatusFilterType extends AbstractApiFilterType
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => UnitOperationalStatusChoiceType::class,
                'active_filter_formatter' => function (FilterData $filterData): ?string {
                    $value = $filterData->getValue();

                    if (null === $value || [] === $value) {
                        return null;
                    }

                    /** @var UnitOperationalStatus[] $unitOperationalStatuses */
                    $unitOperationalStatuses = $this->queryBus->dispatch(new FindAllUnitOperationalStatusQuery());

                    $labelsByIri = [];
                    foreach ($unitOperationalStatuses as $unitOperationalStatus) {
                        $labelsByIri[$unitOperationalStatus->iri] = $this->translator->trans(\sprintf('%s.%s', 'extranet.fields.unit_operational_status', mb_strtolower($unitOperationalStatus->name)));
                    }

                    $iris = \is_array($value) ? $value : [$value];

                    return implode(', ', array_map(static fn (string $iri): string => $labelsByIri[$iri] ?? $iri, $iris));
                },
            ])
        ;
    }
}

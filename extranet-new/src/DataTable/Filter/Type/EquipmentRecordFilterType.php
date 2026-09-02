<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use App\Form\Type\EquipmentRecord\EquipmentRecordAutocompleteType;
use App\Sdk\Resource\EquipmentRecord;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class EquipmentRecordFilterType extends AbstractApiFilterType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $translator = $this->translator;

        $resolver
            ->setDefaults([
                'form_type' => EquipmentRecordAutocompleteType::class,
                'value_extractor' => static fn (EquipmentRecord $equipmentRecord) => $equipmentRecord->serialNumber,
                'active_filter_formatter' => static function (FilterData $filterData) use ($translator) {
                    /** @var EquipmentRecord|array<EquipmentRecord>|null $resource */
                    $resource = $filterData->getValue();

                    if (null === $resource) {
                        return null;
                    }

                    $format = static function (EquipmentRecord $equipmentRecord) use ($translator): string {
                        return $translator->trans('extranet.form.equipment_record_label', [
                            '%serial%' => $equipmentRecord->serialNumber,
                            '%asset%' => $equipmentRecord->customerSerialNumber,
                        ]);
                    };

                    if (\is_array($resource)) {
                        if ([] === $resource) {
                            return null;
                        }

                        return implode(', ', array_map($format, $resource));
                    }

                    return $format($resource);
                },
            ])
        ;
    }
}

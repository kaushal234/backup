<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Persistence;

use AppBundle\Manager\SettingsManager;
use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceAdapterInterface;
use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectInterface;
use Kreyu\Bundle\DataTableBundle\Personalization\PersonalizationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;

readonly class UserSettingsPersistenceAdapter implements PersistenceAdapterInterface
{
    public function __construct(
        private SettingsManager $settingsManager,
        private string $prefix,
    ) {
    }

    public function read(DataTableInterface $dataTable, PersistenceSubjectInterface $subject): mixed
    {
        if (!$subject instanceof UserModulePersistenceSubjectAggregate) {
            throw new \LogicException('Invalid persistence subject type.');
        }

        $key = \sprintf('%s.datatable.%s.%s', mb_strtolower($subject->getModuleName()), $dataTable->getName(), $this->prefix);
        $data = $this->settingsManager->get($key);

        if (!isset($data)) {
            return null;
        }

        // We need a column key in case of personalization.
        return match ($this->prefix) {
            'personalization' => PersonalizationData::fromArray(['columns' => $data]),
            'pagination' => PaginationData::fromArray($data),
            'sorting' => SortingData::fromArray($data),
            'filtration' => FiltrationData::fromArray($data),
            default => throw new \LogicException('Invalid prefix.'),
        };
    }

    public function write(DataTableInterface $dataTable, PersistenceSubjectInterface $subject, mixed $data): void
    {
        if (!$subject instanceof UserModulePersistenceSubjectAggregate) {
            throw new \LogicException('Invalid persistence subject type.');
        }

        // Prefix determines the way to get user data.
        $settings = match ($this->prefix) {
            'personalization' => array_map(static fn ($item) => (array) $item, $data->getColumns()),
            'pagination' => ['page' => $data->getPage(), 'perPage' => $data->getPerPage()],
            'sorting' => $data->getColumns(),
            'filtration' => $data->getFilters(),
            default => throw new \LogicException('Invalid prefix.'),
        };

        $key = \sprintf('%s.datatable.%s.%s', mb_strtolower($subject->getModuleName()), $dataTable->getName(), $this->prefix);

        $this->settingsManager->set($key, $settings);
    }
}

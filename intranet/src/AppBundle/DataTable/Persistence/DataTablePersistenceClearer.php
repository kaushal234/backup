<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Persistence;

use AppBundle\Manager\SettingsManager;
use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectInterface;
use Symfony\Contracts\Cache\CacheInterface;

readonly class DataTablePersistenceClearer implements DataTablePersistenceClearerInterface
{
    public function __construct(
        private CacheInterface $cache,
        private SettingsManager $settingsManager,
    ) {
    }

    public function clear(PersistenceSubjectInterface $subject, string $dataTableName): void
    {
        if (!$subject instanceof UserModulePersistenceSubjectAggregate) {
            throw new \LogicException('Invalid persistence subject type.');
        }

        foreach (['pagination', 'filtration', 'sorting'] as $prefix) {
            $this->cache->delete($this->getCacheKey($subject, $dataTableName, $prefix));
        }

        $key = \sprintf('%s.datatable.%s.personalization', mb_strtolower($subject->getModuleName()), $dataTableName);
        $this->settingsManager->remove($key);
    }

    private function getCacheKey(PersistenceSubjectInterface $subject, string $dataTableName, string $prefix): string
    {
        return urlencode(implode('_', array_filter([
            $dataTableName,
            $prefix,
            $subject->getDataTablePersistenceIdentifier(),
        ])));
    }
}

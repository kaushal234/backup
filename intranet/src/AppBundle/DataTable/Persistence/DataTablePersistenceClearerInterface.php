<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Persistence;

use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectInterface;

interface DataTablePersistenceClearerInterface
{
    public function clear(PersistenceSubjectInterface $subject, string $dataTableName): void;
}

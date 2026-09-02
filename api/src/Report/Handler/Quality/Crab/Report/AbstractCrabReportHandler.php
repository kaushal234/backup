<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\Crab\Report;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

abstract class AbstractCrabReportHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;
    protected IriConverterInterface $iriConverter;
    private array $conditions = [];
    private array $joins = [];
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
    }

    public function getReport(array $options = []): ?ReportDataProvider
    {
        $andSubSelectSQL = $andSQL = $join = $subJoin = '';

        foreach ($this->conditions as $config) {
            if (\is_array($config['value'])) {
                $andSubSelectSQL .= \sprintf(' AND sub_%s.%s IN (%s)', $config['alias'], $config['field'], implode(', ', $config['value']));
                $andSQL .= \sprintf(' AND %s.%s IN (%s)', $config['alias'], $config['field'], implode(', ', $config['value']));

                continue;
            }

            $andSubSelectSQL .= \sprintf(' AND sub_%s.%s = %s', $config['alias'], $config['field'], $config['value']);
            $andSQL .= \sprintf(' AND %s.%s = %s', $config['alias'], $config['field'], $config['value']);
        }

        foreach ($this->joins as $config) {
            $subJoin .= \sprintf(' LEFT JOIN %1$s sub_%2$s ON sub_%2$s.%3$s = sub_%4$s.%5$s', $config['table'], $config['table_alias'], $config['join_field'], $config['juncture_alias'], $config['juncture_table']);
            $join .= \sprintf(' LEFT JOIN %1$s %2$s ON %2$s.%3$s = %4$s.%5$s', $config['table'], $config['table_alias'], $config['join_field'], $config['juncture_alias'], $config['juncture_table']);
        }

        $sql = \sprintf('
SELECT DATE_FORMAT(er.green_tag_date, "%%Y-%%m")                  AS x,
       crab.category                                              AS y,
       ROUND(COUNT(crab.id) / (SELECT count(*)
                        from equipment_records sub_er
                        %s
                        where PERIOD_DIFF(DATE_FORMAT(NOW(), "%%Y%%m"), DATE_FORMAT(sub_er.green_tag_date, "%%Y%%m")) =
                              PERIOD_DIFF(DATE_FORMAT(NOW(), "%%Y%%m"), DATE_FORMAT(er.green_tag_date, "%%Y%%m"))
                        %s
                        ), 1)    AS value
FROM equipment_records er
         LEFT JOIN crab ON (crab.equipment_record_id = er.id)
         LEFT JOIN directory_location dl ON (er.manufacturer_location_id = dl.id)
%s
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), "%%Y%%m"), DATE_FORMAT(er.first_green_tag_date, "%%Y%%m")) <= 12
  AND er.green_tag_date IS NOT NULL
  AND crab.category IS NOT NULL
%s
GROUP BY x, y, PERIOD_DIFF(DATE_FORMAT(NOW(), "%%Y%%m"), DATE_FORMAT(er.first_green_tag_date, "%%Y%%m")) <= 12
ORDER BY DATE_FORMAT(crab.created_at, "%%Y-%%m") ASC', $subJoin, $andSubSelectSQL, $join, $andSQL);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('value', 'value');
        $rsm->addScalarResult('x', 'x');
        $rsm->addScalarResult('y', 'y');

        $query = $this->entityManager->createNativeQuery($sql, $rsm);

        return new ReportDataProvider(
            $query->getScalarResult()
        );
    }

    public function addConditions(string $alias, string $field, array|string|int $value = []): void
    {
        $this->conditions[] = [
            'alias' => $alias,
            'field' => $field,
            'value' => $value,
        ];
    }

    public function addJoins(string $table, string $tableAlias, string $joinField, string $junctureAlias, string $junctureTable): void
    {
        $this->joins[] = [
            'table' => $table,
            'table_alias' => $tableAlias,
            'join_field' => $joinField,
            'juncture_alias' => $junctureAlias,
            'juncture_table' => $junctureTable,
        ];
    }

    public function add($value, $className, $alias, $field): self
    {
        if (!\is_array($value)) {
            $resource = $this->iriConverter->getResourceFromIri($value);
            if ($resource instanceof $className) {
                $this->addConditions($alias, $field, $resource->getId());
            }

            return $this;
        }

        $resources = [];
        foreach ($value as $item) {
            $resource = $this->iriConverter->getResourceFromIri($item);
            if (!$resource instanceof $className) {
                continue;
            }
            $resources[] = $resource->getId();
        }

        $this->addConditions($alias, $field, $resources);

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Report\Handler\Finance;

use App\Entity\Finance\ExchangeRate;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\ResultSetMapping;

class ExchangeRateHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    private readonly EntityManagerInterface $entityManager;
    private string $type;
    private string $currency;
    private ?string $year = null;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (ExchangeRate::class !== $resourceClass || 'applicatedOn' !== $x || 'currency.name' !== $y || !isset($options['type'], $options['currency'])) {
            return null;
        }

        $this->type = $options['type'];
        $this->currency = $options['currency'];
        $this->year = $options['year'] ?? null;

        $parameters = new ArrayCollection();

        $sql = 'yearToDateAverage' === $this->type ? $this->generateYTDReportSQL($parameters) : $this->generateReportSQL($parameters);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('x', 'x');
        $rsm->addScalarResult('y', 'y');
        $rsm->addScalarResult('value', 'value');

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameters($parameters);

        return new ReportDataProvider(
            $query->getScalarResult()
        );
    }

    private function generateReportSQL(ArrayCollection &$parameters): string
    {
        $startDate = (null !== $this->year && '' !== $this->year) ? Chronos::createFromFormat('Y', $this->year)->startOfYear() : Chronos::today()->startOfMonth()->subMonths(12);
        // Setting the limit far in the future if not specified
        $endDate = null !== $this->year ? $startDate->endOfYear() : $startDate->addYears(100);
        $parameters->add(new Parameter('start_date', $startDate->toDateString()));
        $parameters->add(new Parameter('end_date', $endDate->toDateString()));

        $SELECT = 'e.rate';
        if ('EUR' !== $this->currency) {
            $SELECT = 'ROUND(e.rate / (SELECT exr.rate FROM exchange_rates AS exr INNER JOIN currencies cur ON exr.currency_id = cur.id WHERE e.applicated_on = exr.applicated_on AND e.type = exr.type AND cur.name = :currency), 4)';
            $parameters->add(new Parameter('currency', $this->currency));
        }

        $graphType = [
            'endOfMonth' => ExchangeRate::TYPE_END_OF_MONTH_RATE,
            'monthlyAverage' => ExchangeRate::TYPE_MONTH_AVERAGE_RATE,
        ];

        $parameters->add(new Parameter('type', $graphType[$this->type] ?? 'NOP'));

        return \sprintf("SELECT %s AS value, DATE_FORMAT(e.applicated_on, '%%Y-%%m') AS x, c.name AS y FROM exchange_rates e INNER JOIN currencies c ON e.currency_id = c.id WHERE e.applicated_on BETWEEN :start_date AND :end_date AND e.type = :type GROUP BY e.applicated_on, c.name ORDER BY e.applicated_on DESC, c.name ASC", $SELECT);
    }

    private function generateYTDReportSQL(ArrayCollection &$parameters): string
    {
        $startDate = (null !== $this->year && '' !== $this->year) ? Chronos::createFromFormat('Y', $this->year)->startOfYear() : Chronos::today()->startOfMonth()->subMonths(12);
        // Setting the limit far in the future if not specified
        $endDate = $startDate->addMonths(11)->endOfMonth();
        $parameters->add(new Parameter('start_date', $startDate->toDateString()));
        $parameters->add(new Parameter('end_date', $endDate->toDateString()));
        $parameters->add(new Parameter('type', ExchangeRate::TYPE_MONTH_AVERAGE_RATE));

        $factor = '';
        if ('EUR' !== $this->currency) {
            $factor = <<<'SQL'
                 	/
                 (
                 SELECT AVG(rate)
                 FROM exchange_rates AS exr2
                 INNER JOIN currencies cur ON exr2.currency_id = cur.id
                				WHERE exr2.type = :type
                					AND cur.name = :currency
                				 AND YEAR(exr2.applicated_on) = YEAR(e.applicated_on)
                 AND exr2.applicated_on <= e.applicated_on
                 )
                SQL;
            $parameters->add(new Parameter('currency', $this->currency));
        }

        return \sprintf("SELECT ROUND( (SELECT AVG(exr.rate) FROM exchange_rates AS exr WHERE exr.type = :type AND exr.currency_id = e.currency_id AND YEAR(exr.applicated_on) = YEAR(e.applicated_on) AND exr.applicated_on <= e.applicated_on ) %s, 4) AS value, DATE_FORMAT(e.applicated_on, '%%Y-%%m') AS x, c.name as y FROM exchange_rates e INNER JOIN currencies c ON e.currency_id = c.id WHERE e.type = :type GROUP BY e.applicated_on, c.name HAVING e.applicated_on BETWEEN :start_date AND :end_date ORDER BY e.applicated_on DESC, c.name ASC", $factor);
    }
}

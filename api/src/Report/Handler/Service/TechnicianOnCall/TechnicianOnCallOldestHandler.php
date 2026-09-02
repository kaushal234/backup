<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall\OldestReport;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Options\TechnicianOnCallKpiOptions;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

class TechnicianOnCallOldestHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TechnicianOnCallKpiOptions $technicianOnCallKpiOptions,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (OldestReport::class !== $resourceClass || 'month' !== $x || 'oldest' !== $y) {
            return null;
        }

        $options = $this->technicianOnCallKpiOptions->configureOptions($options);
        $repository = $this->entityManager->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('r');

        $queryBuilder
            ->select("DATE_FORMAT(r.date, '%Y-%m') AS x")
            ->addSelect('l.name AS y')
            ->addSelect('r.days AS value')
            ->addSelect('t.id AS id')
            ->join('r.salesOrganisation', 'l')
            ->join('r.technicianOnCall', 't')
            ->where('r.date >= :from')
            ->andWhere('r.date < :to')
            ->groupBy('r.salesOrganisation', 'x')
            ->orderBy('x')
            ->setParameter('from', $options['from'])
            ->setParameter('to', $options['to'])
        ;

        if ($options['salesServiceOrganisation']) {
            $queryBuilder
                ->andWhere('r.salesOrganisation = :sso_id')
                ->setParameter('sso_id', $options['salesServiceOrganisation'])
            ;
        }

        if ($options['customers']) {
            $queryBuilder
                ->andWhere('t.customer in (:customers)')
                ->setParameter('customers', $options['customers'], ArrayParameterType::INTEGER)
            ;
        }

        $provider = new ReportDataProvider(
            $queryBuilder->getQuery()->getResult()
        );

        return $provider->setMetadataExtractor(static function (array $results) {
            $yIris = [];
            foreach ($results as $result) {
                $yIris[$result['y']] = $result['id'];
            }

            return [
                ReportHandlerInterface::METADATA_IRIS_Y_KEY => $yIris,
            ];
        });
    }
}

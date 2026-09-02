<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\CustomerServiceRecord;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Options\TechnicianOnCallKpiOptions;
use Doctrine\ORM\EntityManagerInterface;

class CustomerServiceRecordBacklogHandler implements ReportHandlerInterface
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
        if (AbstractCustomerServiceRecord::class !== $resourceClass || 'year' !== $x || 'backlog' !== $y) {
            return null;
        }

        $options = $this->technicianOnCallKpiOptions->configureOptions($options);

        $repository = $this->entityManager->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('c');

        $queryBuilder
            ->select("DATE_FORMAT(c.createdAt, '%Y') AS x")
            ->addSelect('TYPE(c) AS y')
            ->addSelect('COUNT(c) AS value')
            ->where($queryBuilder->expr()->in('c.status', ':statuses'))
            ->groupBy('x', 'y')
            ->orderBy('x')
            ->setParameter('statuses', [...AbstractCustomerServiceRecord::OPEN_STATUSES, AbstractCustomerServiceRecord::IN_PROGRESS])
        ;

        if ($options['salesServiceOrganisation']) {
            $queryBuilder
                ->join('c.equipmentRecord', 'e')
                ->andWhere('e.salesOrganisationService = :sso_id')
                ->setParameter('sso_id', $options['salesServiceOrganisation'])
            ;
        }

        return new ReportDataProvider(
            $queryBuilder->getQuery()->getResult()
        );
    }
}

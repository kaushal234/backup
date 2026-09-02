<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\TechnicianOnCall;

use App\Entity\Directory\Location;
use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallSurveyHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (TechnicianOnCallSurvey::class !== $resourceClass || 'salesOrganisationService.name' !== $x || 'quantity' !== $y) {
            return null;
        }

        $repository = $this->entityManager->getRepository($resourceClass);

        $queryBuilder = $repository->createQueryBuilder('ts');

        $queryBuilder
            ->select('l.name AS location')
            ->addSelect('COUNT(t) as total')
            ->addSelect('ROUND(AVG(ts.execution), 1) as execution')
            ->addSelect('ROUND(AVG(ts.responsiveness), 1) as responsiveness')
            ->addSelect('ROUND(AVG(ts.communication), 1) as communication')
            ->addSelect('ROUND(AVG(ts.attitude), 1) as attitude')
            ->addSelect('l.id AS sso_id')
            ->join('ts.technicianOnCall', 't')
            ->join('t.salesOrganisationService', 'l')
            ->where('t.solvedAt >= :from')
            ->andWhere('t.solvedAt < :to')
            ->groupBy('l.name')
            ->setParameter('from', (new \DateTime('first day of this months'))->setTime(0, 0)->format('Y-m-d H:i:s'))
            ->setParameter('to', (new \DateTime('first day of next months'))->setTime(23, 59, 59)->format('Y-m-d H:i:s'))
        ;

        $data = [];
        foreach ($queryBuilder->getQuery()->getResult() as $row) {
            foreach (['attitude', 'communication', 'responsiveness', 'execution', 'total'] as $key) {
                $data[] = [
                    'y' => $this->translator->trans('toc.fields.survey.'.$key, [], 'technician_on_call'),
                    'x' => $row['location'],
                    'value' => $row[$key],
                    'sso_id' => $row['sso_id'],
                ];
            }
        }

        return (new ReportDataProvider($data))->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Location::class, 'sso_id')
            ->generate()
        );
    }
}

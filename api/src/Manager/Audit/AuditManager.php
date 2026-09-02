<?php

declare(strict_types=1);

namespace App\Manager\Audit;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Dto\Audit\AuditLogProperty;
use App\Entity\AuditLog;
use App\Entity\Directory\People;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

readonly class AuditManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DenormalizerInterface $denormalizer,
        private IriConverterInterface $iriConverter,
    ) {
    }

    public function getAuditLogsFiltered(array $ids, string $type, string $property): array
    {
        return $this->denormalize($this->getQueryBuilder($ids, $type, $property));
    }

    public function getAuditLogsFilteredByMonth(array $ids, string $type, string $property): array
    {
        $queryBuilder = $this->getQueryBuilder($ids, $type, $property);

        $queryBuilder
            ->addSelect("DATE_FORMAT(al.createdAt,'%Y-%m') as month")
            ->andWhere($queryBuilder->expr()->gt('al.createdAt', ':one_year_ago'))
            ->groupBy('al.value, month')
            ->setParameter('one_year_ago', (new \DateTime('1 year ago'))->format('Y-m-d'))
        ;

        return $this->denormalize($queryBuilder);
    }

    public function getAuditLogsFilteredByReference(array $ids, string $type, string $property): array
    {
        $queryBuilder = $this->getQueryBuilder($ids, $type, $property);

        $queryBuilder
            ->addSelect('p.id as createdBy')
            ->addSelect("DATE_FORMAT(al.createdAt, '%Y-%m-%d') as createdAt")
            ->leftJoin(People::class, 'p', Join::ON, 'p = al.createdBy')
            ->groupBy('al.id')
        ;

        return $this->denormalize($queryBuilder);
    }

    public function getAuditLogsFilteredTime(array $ids, string $type, string $property): array
    {
        $queryBuilder = $this->getQueryBuilder($ids, $type, $property);
        $queryBuilder
            ->select('al.value')
            ->addSelect('SUM(CAST(TIMESTAMP_DIFF(SECOND, al.createdAt, CASE WHEN next.createdAt is null THEN NOW() ELSE next.createdAt END ) AS FLOAT)) AS time')
        ;

        return $this->denormalize($queryBuilder);
    }

    private function getQueryBuilder(array $ids, string $type, string $property): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder
            ->select('al.value')
            ->addSelect('al.referenceId')
            ->addSelect('AVG(CAST(TIMESTAMP_DIFF(SECOND, al.createdAt,next.createdAt) AS FLOAT)) AS time')
            ->from(AuditLog::class, 'al')
            ->leftJoin(AuditLog::class, 'next', Join::ON, 'next.id = al.next')
            ->where($queryBuilder->expr()->eq('al.auditType', ':type'))
            ->andWhere($queryBuilder->expr()->eq('al.property', ':property'))
            ->groupBy('al.value')
            ->setParameters(new ArrayCollection([
                new Parameter('type', $type),
                new Parameter('property', $property),
            ]))
        ;

        if ([] !== $ids) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->in('al.referenceId', ':ids'))
                ->setParameter('ids', $ids)
            ;
        }

        return $queryBuilder;
    }

    private function denormalize(QueryBuilder $queryBuilder): array
    {
        $results = [];

        foreach ($queryBuilder->getQuery()->getArrayResult() as $row) {
            if (null !== ($row['createdBy'] ?? null)) {
                $row['createdBy'] = $this->iriConverter->getIriFromResource(People::class, UrlGeneratorInterface::ABS_PATH, new Get(), ['uri_variables' => ['id' => $row['createdBy']]]);
            }
            $results[] = $this->denormalizer->denormalize($row, AuditLogProperty::class, null, [ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]);
        }

        return $results;
    }
}

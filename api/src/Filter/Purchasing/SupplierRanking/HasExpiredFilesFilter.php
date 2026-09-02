<?php

declare(strict_types=1);

namespace App\Filter\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Entity\Purchasing\SupplierRanking\SupplierRankingFile;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class HasExpiredFilesFilter implements FilterInterface
{
    public const string FILTER_PROPERTY = 'hasExpiredFiles';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (SupplierRanking::class !== $resourceClass) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(self::FILTER_PROPERTY)) {
            return;
        }

        $rawValue = $request->query->get(self::FILTER_PROPERTY);
        if (null === $rawValue) {
            return;
        }

        $value = $this->normalizeValue($rawValue);
        if (null === $value) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0] ?? null;
        if (null === $rootAlias) {
            return;
        }

        $filesAlias = $queryNameGenerator->generateJoinAlias('expired_files');
        $expiredAtParam = $queryNameGenerator->generateParameterName('expired_at');

        $subQueryBuilder = $queryBuilder->getEntityManager()->createQueryBuilder();
        $subQueryBuilder
            ->select('1')
            ->from(SupplierRankingFile::class, $filesAlias)
            ->where(\sprintf('%s.supplierRanking = %s', $filesAlias, $rootAlias))
            ->andWhere(\sprintf('%s.expiredAt IS NOT NULL', $filesAlias))
            ->andWhere(\sprintf('%s.expiredAt < :%s', $filesAlias, $expiredAtParam));

        $queryBuilder
            ->setParameter($expiredAtParam, new \DateTimeImmutable('now'));

        $queryBuilder->andWhere($value ? \sprintf('EXISTS (%s)', $subQueryBuilder->getDQL()) : \sprintf('NOT EXISTS (%s)', $subQueryBuilder->getDQL()));
    }

    public function getDescription(string $resourceClass): array
    {
        if (SupplierRanking::class !== $resourceClass) {
            return [];
        }

        return [
            self::FILTER_PROPERTY => [
                'property' => self::FILTER_PROPERTY,
                'type' => 'bool',
                'required' => false,
                'description' => 'Filter supplier rankings that have at least one expired file.',
            ],
        ];
    }

    private function normalizeValue(mixed $value): ?bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        if (\is_int($value)) {
            return 1 === $value;
        }

        if (\is_string($value)) {
            $normalized = mb_strtolower(mb_trim($value));
            if (\in_array($normalized, ['1', 'true', 'yes', 'y'], true)) {
                return true;
            }

            if (\in_array($normalized, ['0', 'false', 'no', 'n'], true)) {
                return false;
            }
        }

        return null;
    }
}

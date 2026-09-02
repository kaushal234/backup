<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Purchasing;

use App\Entity\Purchasing\SupplierRanking\Criteria;
use App\Entity\Purchasing\SupplierRanking\FileCategory;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Entity\Purchasing\SupplierRanking\SupplierRankingFile;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;
use Doctrine\ORM\EntityManagerInterface;

class SupplierRankingSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    /** @var array<string> */
    private array $criterias = [];

    /** @var array<string> */
    private array $fileCategories = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getComputedColumns(): array
    {
        if (0 === \count($this->criterias)) {
            $repository = $this->entityManager->getRepository(Criteria::class);
            foreach ($repository->findAll() as $criteria) {
                $this->criterias[] = $this->normalizeColumnName($criteria->name);
            }
        }

        if ([] === $this->fileCategories) {
            $repository = $this->entityManager->getRepository(FileCategory::class);

            foreach ($repository->findAll() as $fileCategory) {
                $this->fileCategories[] = $this->normalizeColumnName($fileCategory->name);
            }
        }

        return array_merge(
            $this->criterias,
            $this->fileCategories,
        );
    }

    /**
     * @param SupplierRanking $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        foreach ($item->getNotations() as $notation) {
            if ($this->normalizeColumnName($notation->criteria->name) === $column) {
                return $notation->notation;
            }
        }

        if (\in_array($column, $this->getFileCategories(), true)) {
            return $this->getFileCategoryStatus($item, $column);
        }

        return null;
    }

    public function supports(string $class, string $operationName): bool
    {
        return SupplierRanking::class === $class;
    }

    protected function getColumnToRename(): array
    {
        return [
            'supplier.country' => 'supplier country',
            'supplier.location' => 'master bu',
            'supplier.masterBuyer' => 'buyer',
            'expertiseLevel.name' => 'expertise level',
            'revenue' => 'total revenue last year',
            'supplier.currency' => 'currency',
            'classification.name' => 'classification',
        ];
    }

    private function getFileCategoryStatus(SupplierRanking $supplierRanking, string $column): string
    {
        $hasFile = false;

        /** @var SupplierRankingFile $file */
        foreach ($supplierRanking->getFiles() as $file) {
            if ($this->normalizeColumnName($file->category->name) !== $column) {
                continue;
            }

            $hasFile = true;

            if ($file->isExpired()) {
                return 'expired';
            }
        }

        return $hasFile ? 'valid' : '';
    }

    /**
     * @return array<string>
     */
    private function getFileCategories(): array
    {
        if ([] === $this->fileCategories) {
            $repository = $this->entityManager->getRepository(FileCategory::class);

            foreach ($repository->findAll() as $fileCategory) {
                $this->fileCategories[] = $this->normalizeColumnName($fileCategory->name);
            }
        }

        return $this->fileCategories;
    }

    private function normalizeColumnName(string $value): string
    {
        // Lowercase
        $value = mb_strtolower($value);

        // Replace & by 'and' for consistency
        $value = str_replace('&', 'and', $value);

        // Remove 'and' as a word
        $value = preg_replace('/\band\b/', '', $value);

        // Remove commas
        $value = str_replace(',', '', $value);

        // Replace non alphanumeric by space
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value);

        // Trim + split words
        $words = preg_split('/\s+/', mb_trim($value));

        // Build camelCase
        $camelCase = array_shift($words);
        foreach ($words as $word) {
            $camelCase .= ucfirst($word);
        }

        return $camelCase;
    }
}

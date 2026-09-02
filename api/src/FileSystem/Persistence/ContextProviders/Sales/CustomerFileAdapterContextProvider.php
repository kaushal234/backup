<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Sales;

use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class CustomerFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return CustomerFile::class;
    }

    public function getFileProperty(): string
    {
        return 'customerFiles';
    }

    public function getDirectory(): string
    {
        return 'sales/customers';
    }

    /**
     * @param Customer $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%07s-%s-%s.%s',
            $subject->getId(),
            $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            Utf8Slugger::slugify($context['description'] ?? $context['filename']),
            $context['extension']
        );
    }

    protected function getMaxSize(): ?string
    {
        return '20M';
    }
}

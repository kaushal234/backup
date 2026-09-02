<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Service;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecordFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class CustomerServiceRecordFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return CustomerServiceRecordFile::class;
    }

    public function getDirectory(): string
    {
        return 'service/customer_service_record';
    }

    /**
     * @param AbstractCustomerServiceRecord $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%07s-%s-%s.%s',
            $subject->getId(),
            $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            Utf8Slugger::slugify($context['filename']),
            $context['extension']
        );
    }

    protected function getMaxSize(): ?string
    {
        return '15M';
    }

    protected function getMimeTypes(): array
    {
        return [...parent::getMimeTypes(), ...['application/zip', 'application/x-zip-compressed', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.ms-powerpoint', 'video/mp4']];
    }
}

<?php

declare(strict_types=1);

namespace App\Emailable\Adapter;

use App\Emailable\EmailMetadataInterface;
use App\Entity\Quality\CalibratedTools\Tool;

class EmailableToolAdapter implements EmailMetadataInterface
{
    private readonly Tool $tool;

    public function __construct(Tool $tool)
    {
        $this->tool = $tool;
    }

    public function getEmailData(): array
    {
        return [
            'tool.fields.serialNumber' => $this->tool->getSerialNumber(),
            'tool.fields.content' => $this->tool->getDescription(),
        ];
    }

    public function getEmailSubject(): string
    {
        return 'Tool subject';
    }

    public function getTranslationDomain(): string
    {
        return 'tool';
    }

    public static function supports($resource): bool
    {
        return \is_object($resource) && $resource instanceof Tool;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Mailer;

use App\Emailable\EmailMetadataInterface;

final class DummyEmailMetadataAdapter implements EmailMetadataInterface
{
    public static function supports($resource): bool
    {
        return \is_object($resource)
            && $resource instanceof DummyObject
            && $resource->is('Dummy');
    }

    public function getEmailData(): array
    {
        return [];
    }

    public function getEmailSubject(): string
    {
        return '';
    }

    public function getTranslationDomain(): string
    {
        return '';
    }
}

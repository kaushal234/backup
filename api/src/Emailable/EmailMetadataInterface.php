<?php

declare(strict_types=1);

namespace App\Emailable;

interface EmailMetadataInterface
{
    public static function supports($resource): bool;

    /**
     * return an array of data that will be transformed into a table in email body.
     */
    public function getEmailData(): array;

    /**
     * return the subject of the email.
     */
    public function getEmailSubject(): string;

    /**
     * return the translation domain used to translate the keys in email template.
     */
    public function getTranslationDomain(): string;
}

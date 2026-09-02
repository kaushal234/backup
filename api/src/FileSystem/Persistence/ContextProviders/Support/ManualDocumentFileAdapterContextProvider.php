<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Support;

use App\Entity\Support\ManualDocument;
use App\Entity\Support\ManualDocumentFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use App\Validator\Constraints\ManualDocumentFileValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class ManualDocumentFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public function getFileConstraint($subject): Constraint
    {
        /** @var FileConstraint $constraint */
        $constraint = parent::getFileConstraint($subject);
        $constraint->mimeTypes = ManualDocumentFileValidator::getValidMimeTypeForType($subject->type);

        if (null !== $maxSize = ManualDocumentFileValidator::getSizeForType($subject->type, 'MB')) {
            $constraint->maxSize = $maxSize.'m';
        }

        return $constraint;
    }

    public static function getClass(): string
    {
        return ManualDocumentFile::class;
    }

    public function getDirectory(): string
    {
        return 'manuals_diagrams';
    }

    public function getFileProperty(): string
    {
        return 'document';
    }

    /**
     * @param ManualDocument|object $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        $factoryNumber = (null !== $subject->factoryNumber) ? mb_trim((string) $subject->factoryNumber) : '';
        $revision = (null !== $subject->revision) ? mb_trim((string) $subject->revision) : '';
        $filename = $factoryNumber.$revision;

        return \sprintf('%s-%s.%s', time(), $filename, $context['extension']);
    }

    protected function getMimeTypes(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
        ];
    }

    protected function getMaxSize(): ?string
    {
        return null;
    }
}

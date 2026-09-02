<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\Support\ManualDocument;
use App\Entity\Support\ManualDocumentFile;
use App\Validator\Constraints\ManualDocumentFileValidator;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ManualDocumentFileValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider sizeProvider
     */
    public function testCalculateFileSize($type, $unit, $expected)
    {
        $validator = new ManualDocumentFileValidator();
        $result = $validator::getSizeForType($type, $unit);

        $this->assertSame($expected, $result);
    }

    public function sizeProvider()
    {
        yield 'Manual section' => ['MANUAL SECTION', 'KB', null];
        yield 'Undefined' => ['undefined', 'KB', null];
        yield 'Parts diagram B' => ['PARTS DIAGRAM', 'B', 1000000000];
        yield 'Parts diagram MB' => ['PARTS DIAGRAM', 'MB', 1000];
    }

    /**
     * @dataProvider mimeTypeByDocumentTypeProvider
     */
    public function testAvailableMimeType($type, $expected): void
    {
        $this->assertSame($expected, ManualDocumentFileValidator::getValidMimeTypeForType($type));
    }

    public function mimeTypeByDocumentTypeProvider(): \Generator
    {
        yield 'Type MANUAL SECTION' => [ManualDocument::MANUAL_SECTION, 'application/pdf'];
        yield 'Type PARTS DIAGRAM' => [ManualDocument::PARTS_DIAGRAM, 'image/jpeg'];
        yield 'Type undefined' => ['undefined', null];
    }

    public function testPartDiagramJpgSizeForNonCriticalGroup(): void
    {
        $manualDocument = new ManualDocument();
        $manualDocument->type = ManualDocument::PARTS_DIAGRAM;
        $manualDocument->factoryNumber = 'factoryNumber';
        $manualDocument->revision = 'A';

        $manualDocumentFile = new ManualDocumentFile();
        $manualDocumentFile->setManualDocument($manualDocument);
        $manualDocumentFile->setSize(1000001);

        $this->validator->validate($manualDocumentFile, new \App\Validator\Constraints\ManualDocumentFile(options: null, groups: ['noncritical'], payload: null));
        $this->buildViolation('The size of the JPG associated with the part number factoryNumber revision A must not exceed 1M')->assertRaised();
    }

    public function testPartDiagramFileForCriticalGroupHaveRequiredSize(): void
    {
        $manualDocument = new ManualDocument();
        $manualDocument->type = ManualDocument::PARTS_DIAGRAM;
        $manualDocument->factoryNumber = 'factoryNumber';
        $manualDocument->revision = 'A';

        $manualDocumentFile = new ManualDocumentFile();
        $manualDocumentFile->setManualDocument($manualDocument);
        $manualDocumentFile->setSize(999999);

        $this->validator->validate($manualDocumentFile, new \App\Validator\Constraints\ManualDocumentFile(options: null, groups: ['noncritical'], payload: null));
        $this->assertNoViolation();
    }

    public function testManualSectionFileMimeTypeForNonCriticalGroup(): void
    {
        $manualDocument = new ManualDocument();
        $manualDocument->type = ManualDocument::MANUAL_SECTION;
        $manualDocument->factoryNumber = 'factoryNumber';
        $manualDocument->revision = 'A';

        $manualDocumentFile = new ManualDocumentFile();
        $manualDocumentFile->setManualDocument($manualDocument);
        $manualDocumentFile->setMimeType('image/jpeg');

        $this->validator->validate($manualDocumentFile, new \App\Validator\Constraints\ManualDocumentFile(options: null, groups: ['noncritical'], payload: null));
        $this->buildViolation('The file associated with the part number factoryNumber revision A is not a PDF file')->assertRaised();
    }

    public function testManualSectionFileForNonCriticalGroupHaveRightMimeType(): void
    {
        $manualDocument = new ManualDocument();
        $manualDocument->type = ManualDocument::MANUAL_SECTION;
        $manualDocument->factoryNumber = 'factoryNumber';
        $manualDocument->revision = 'A';

        $manualDocumentFile = new ManualDocumentFile();
        $manualDocumentFile->setManualDocument($manualDocument);
        $manualDocumentFile->setMimeType('application/pdf');

        $this->validator->validate($manualDocumentFile, new \App\Validator\Constraints\ManualDocumentFile(options: null, groups: ['noncritical'], payload: null));
        $this->assertNoViolation();
    }

    public function testPartDiagramsFileMimeTypeForCriticalGroup(): void
    {
        $manualDocument = new ManualDocument();
        $manualDocument->type = ManualDocument::PARTS_DIAGRAM;
        $manualDocument->factoryNumber = 'factoryNumber';
        $manualDocument->revision = 'A';

        $manualDocumentFile = new ManualDocumentFile();
        $manualDocumentFile->setManualDocument($manualDocument);
        $manualDocumentFile->setMimeType('image/png');

        $this->validator->validate($manualDocumentFile, new \App\Validator\Constraints\ManualDocumentFile(options: null, groups: ['critical'], payload: null));
        $this->buildViolation('The file associated with the part number factoryNumber revision A is not a JPG file')->assertRaised();
    }

    public function testPartDiagramsFileForCriticalGroupHaveRightMimeType(): void
    {
        $manualDocument = new ManualDocument();
        $manualDocument->type = ManualDocument::PARTS_DIAGRAM;
        $manualDocument->factoryNumber = 'factoryNumber';
        $manualDocument->revision = 'A';

        $manualDocumentFile = new ManualDocumentFile();
        $manualDocumentFile->setManualDocument($manualDocument);
        $manualDocumentFile->setMimeType('image/jpeg');

        $this->validator->validate($manualDocumentFile, new \App\Validator\Constraints\ManualDocumentFile(options: null, groups: ['critical'], payload: null));
        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new ManualDocumentFileValidator();
    }
}

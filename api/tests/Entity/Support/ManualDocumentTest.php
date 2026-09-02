<?php

declare(strict_types=1);

namespace App\Tests\Entity\Support;

use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use App\Entity\Support\ManualDocumentFile;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ManualDocumentTest extends KernelTestCase
{
    /**
     * @dataProvider nonCriticalProvider
     */
    public function testValidateTheNonCriticalGroup(string $type, ?ManualDocumentFile $file, int $quantity, bool $hasViolation, ?string $message = null)
    {
        $container = self::getContainer();
        $validator = $container->get(ValidatorInterface::class);
        $manualDocument = new ManualDocument();
        $manualDocument->type = $type;
        $manualDocument->factoryNumber = '000';
        $manualDocument->quantity = $quantity;
        $manualDocument->revision = 'A';

        if ($file instanceof ManualDocumentFile) {
            $manualDocument->addFile($file);
        }

        $errors = $validator->validate($manualDocument, null, [Manual::NONCRITICAL_VALIDATION_GROUP]);

        if ($hasViolation) {
            $this->assertCount(1, $errors);
            $this->assertSame($message, $errors->get(0)->getMessage());
        } else {
            $this->assertCount(0, $errors);
        }
    }

    public function nonCriticalProvider()
    {
        yield 'Document without file is not possible' => [
            'ELEC SCHEM',
            null,
            0,
            true,
            'Jpg file not found for this item 000 revision A',
        ];

        yield 'Document parts diagram without file is not possible' => [
            ManualDocument::PARTS_DIAGRAM,
            null,
            0,
            true,
            'Jpg file not found for this item 000 revision A',
        ];

        $manualDocumentFile = new ManualDocumentFile();
        $manualDocumentFile->setSize(20);
        $manualDocumentFile->setMimeType('image/jpeg');

        yield 'Negative quantity for a non part diagrams document should be possible' => [
            'ELEC SCHEM',
            $manualDocumentFile,
            -1,
            false,
        ];

        $manualDocumentFile = new ManualDocumentFile();
        $manualDocumentFile->setSize(20);
        $manualDocumentFile->setMimeType('image/jpeg');

        yield 'Negative quantity for parts diagram document should not be possible' => [
            ManualDocument::PARTS_DIAGRAM,
            $manualDocumentFile,
            -1,
            true,
            'This value should be greater than 0.',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\FileSystem\Persistence;

use App\Entity\SPQ\AttachedFile;
use App\FileSystem\FileHashGenerator;
use App\FileSystem\Persistence\PersistedFileFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

class PersistedFileFactoryTest extends TestCase
{
    public function testCreatePDFFile()
    {
        $pdf = new File('tests/fixtures/file.pdf');

        $persistedFileFactory = new PersistedFileFactory(new FileHashGenerator());

        $file = $persistedFileFactory->create($pdf, new AttachedFile());

        self::assertSame(303_295, $file->getSize());
        self::assertSame('application/pdf', $file->getMimeType());
        self::assertSame('pdf', $file->getExtension());

        $expected = $pdf->getPath().'/'.$pdf->getFilename();
        self::assertSame(hash_file('sha256', $expected), $file->getSha());
    }

    public function testCreateMsgFile()
    {
        $msg = new File('tests/fixtures/file.msg');

        $persistedFileFactory = new PersistedFileFactory(new FileHashGenerator());

        $file = $persistedFileFactory->create($msg, new AttachedFile());

        self::assertSame(26_112, $file->getSize());
        self::assertSame('application/vnd.ms-outlook', $file->getMimeType());
        self::assertSame('msg', $file->getExtension());

        $expected = $msg->getPath().'/'.$msg->getFilename();
        self::assertSame(hash_file('sha256', $expected), $file->getSha());
    }
}

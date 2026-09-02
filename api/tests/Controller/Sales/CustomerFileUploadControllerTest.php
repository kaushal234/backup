<?php

declare(strict_types=1);

namespace App\Tests\Controller\Sales;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Controller\Sales\CustomerFileUploadController;
use App\Entity\Sales\CustomerFile;
use App\FileSystem\Persistence\PersistableFileManager;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Expected functionality: submitting a customer file with the "is contract" checkbox ticked must mark the
 * persisted file accordingly (so the rest of the flow can trigger AI extraction / contract creation).
 * Leaving the checkbox unticked must leave the file as a regular customer file, exactly as before this
 * feature existed.
 */
final class CustomerFileUploadControllerTest extends TestCase
{
    private const string PDF_FIXTURE = __DIR__.'/../../fixtures/file.pdf';

    public function testCheckingTheContractCheckboxMarksTheUploadedFileAsAContract(): void
    {
        $metadata = $this->invokeControllerAndCaptureMetadata([
            'description' => 'A contract file',
            'isContract' => '1',
        ]);

        self::assertArrayHasKey('isContract', $metadata);
        self::assertSame('1', $metadata['isContract'], 'Ticking the "is contract" checkbox must be forwarded to the persisted file so the contract-creation flow can be triggered.');
    }

    public function testLeavingTheContractCheckboxUncheckedKeepsARegularFile(): void
    {
        $metadata = $this->invokeControllerAndCaptureMetadata([
            'description' => 'A regular file',
        ]);

        self::assertArrayNotHasKey('isContract', $metadata, 'A file submitted without ticking the checkbox must not be treated as a contract file.');
    }

    /**
     * @param array<string, string> $requestFields
     *
     * @return array<string, mixed>
     */
    private function invokeControllerAndCaptureMetadata(array $requestFields): array
    {
        $capturedMetadata = null;

        $manager = $this->createMock(PersistableFileManager::class);
        $manager->expects(self::once())
            ->method('attach')
            ->willReturnCallback(static function ($attachable, $file, array $metadata) use (&$capturedMetadata) {
                $capturedMetadata = $metadata;

                return $attachable;
            });

        $factory = $this->createMock(PersistableFileManagerFactory::class);
        $factory->method('getManagerForClass')->willReturn($manager);

        $controller = new CustomerFileUploadController(
            $factory,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(IriConverterInterface::class),
        );

        $data = new class {
            public function getCustomerFiles(): ArrayCollection
            {
                return new ArrayCollection([new CustomerFile()]);
            }
        };

        $request = new Request(
            request: $requestFields,
            files: ['file' => new UploadedFile(self::PDF_FIXTURE, 'contract.pdf', 'application/pdf', test: true)],
        );

        $controller($data, 'getCustomerFiles', 'App\Entity\Sales\CustomerFile', $request);

        self::assertIsArray($capturedMetadata, 'The file manager must have been called with the upload metadata.');

        return $capturedMetadata;
    }
}

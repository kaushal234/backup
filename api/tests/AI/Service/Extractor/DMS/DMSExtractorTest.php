<?php

declare(strict_types=1);

namespace App\Tests\AI\Service\Extractor\DMS;

use App\AI\Exception\AccessDeniedException;
use App\AI\Exception\EntityNotFoundException;
use App\AI\Security\EntityAccessCheckerInterface;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\AI\Service\Extractor\DMS\DMSExtractor;
use App\AI\Service\FileTextExtractor;
use App\Entity\DMS;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class DMSExtractorTest extends TestCase
{
    private const string FIXTURES_DIR = __DIR__.'/../../../../fixtures';
    private const string FIXTURE_FILE = 'file.txt';

    public function testExtractReturnsFileTextExtractorOutput(): void
    {
        $dms = $this->createDMS(filepath: self::FIXTURE_FILE, mimetype: 'text/plain');

        $fileTextExtractor = $this->createMock(FileTextExtractor::class);
        $fileTextExtractor->expects(self::once())
            ->method('extract')
            ->with(self::FIXTURES_DIR.'/'.self::FIXTURE_FILE)
            ->willReturn('file content');

        $extractor = new DMSExtractor(
            $this->makeRegistry($dms, ['legacyId' => 7]),
            [],
            $this->createMock(SerializerInterface::class),
            $this->makeAccessCheckers(true),
            $this->makeParameters(self::FIXTURES_DIR),
            $fileTextExtractor,
        );

        self::assertSame('file content', $extractor->extract(DMS::class, ['legacyId' => 7]));
    }

    public function testExtractReturnsEmptyStringWhenFileTextExtractorReturnsNull(): void
    {
        $dms = $this->createDMS(filepath: self::FIXTURE_FILE, mimetype: 'text/plain');

        $fileTextExtractor = $this->createMock(FileTextExtractor::class);
        $fileTextExtractor->method('extract')->willReturn(null);

        $extractor = new DMSExtractor(
            $this->makeRegistry($dms, ['legacyId' => 7]),
            [],
            $this->createMock(SerializerInterface::class),
            $this->makeAccessCheckers(true),
            $this->makeParameters(self::FIXTURES_DIR),
            $fileTextExtractor,
        );

        self::assertSame('', $extractor->extract(DMS::class, ['legacyId' => 7]));
    }

    public function testExtractThrowsEntityNotFoundWhenDMSMissing(): void
    {
        $extractor = new DMSExtractor(
            $this->makeRegistry(null, ['legacyId' => 99]),
            [],
            $this->createMock(SerializerInterface::class),
            $this->makeAccessCheckers(true),
            $this->makeParameters(self::FIXTURES_DIR),
            $this->createMock(FileTextExtractor::class),
        );

        $this->expectException(EntityNotFoundException::class);
        $extractor->extract(DMS::class, ['legacyId' => 99]);
    }

    public function testExtractThrowsEntityNotFoundWhenDMSHasNoFilepath(): void
    {
        $dms = $this->createDMS(filepath: null, mimetype: 'text/plain');

        $extractor = new DMSExtractor(
            $this->makeRegistry($dms, ['legacyId' => 7]),
            [],
            $this->createMock(SerializerInterface::class),
            $this->makeAccessCheckers(true),
            $this->makeParameters(self::FIXTURES_DIR),
            $this->createMock(FileTextExtractor::class),
        );

        $this->expectException(EntityNotFoundException::class);
        $extractor->extract(DMS::class, ['legacyId' => 7]);
    }

    public function testExtractThrowsAccessDeniedWhenRegistryDenies(): void
    {
        $dms = $this->createDMS(filepath: self::FIXTURE_FILE, mimetype: 'text/plain');

        $fileTextExtractor = $this->createMock(FileTextExtractor::class);
        $fileTextExtractor->expects(self::never())->method('extract');

        $extractor = new DMSExtractor(
            $this->makeRegistry($dms, ['legacyId' => 7]),
            [],
            $this->createMock(SerializerInterface::class),
            $this->makeAccessCheckers(false),
            $this->makeParameters(self::FIXTURES_DIR),
            $fileTextExtractor,
        );

        $this->expectException(AccessDeniedException::class);
        $extractor->extract(DMS::class, ['legacyId' => 7]);
    }

    public function testExtractThrowsEntityNotFoundWhenFileDoesNotExist(): void
    {
        $dms = $this->createDMS(filepath: 'does-not-exist.txt', mimetype: 'text/plain');

        $fileTextExtractor = $this->createMock(FileTextExtractor::class);
        $fileTextExtractor->expects(self::never())->method('extract');

        $extractor = new DMSExtractor(
            $this->makeRegistry($dms, ['legacyId' => 7]),
            [],
            $this->createMock(SerializerInterface::class),
            $this->makeAccessCheckers(true),
            $this->makeParameters(self::FIXTURES_DIR),
            $fileTextExtractor,
        );

        $this->expectException(EntityNotFoundException::class);
        $extractor->extract(DMS::class, ['legacyId' => 7]);
    }

    private function createDMS(?string $filepath, ?string $mimetype): DMS
    {
        $dms = $this->createMock(DMS::class);
        $dms->method('getFilepath')->willReturn($filepath);
        $dms->method('getMimetype')->willReturn($mimetype);
        $dms->method('getId')->willReturn(7);

        return $dms;
    }

    /**
     * @param array<string,mixed> $criteria
     */
    private function makeRegistry(?DMS $dms, array $criteria): ManagerRegistry
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with($criteria)->willReturn($dms);

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects(self::once())->method('getRepository')->with(DMS::class)->willReturn($repository);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::once())->method('getManagerForClass')->with(DMS::class)->willReturn($manager);

        return $registry;
    }

    private function makeParameters(string $uploadDir): ParameterBagInterface
    {
        $parameters = $this->createMock(ParameterBagInterface::class);
        $parameters->method('get')->with('legacy.upload_dir')->willReturn($uploadDir);

        return $parameters;
    }

    private function makeAccessCheckers(bool $granted): EntityAccessCheckerRegistry
    {
        $checker = $this->createMock(EntityAccessCheckerInterface::class);
        $checker->method('supports')->willReturn(true);
        $checker->method('isGranted')->willReturn($granted);

        return new EntityAccessCheckerRegistry([$checker]);
    }
}

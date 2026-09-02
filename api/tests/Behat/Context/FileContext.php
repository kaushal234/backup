<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\File;
use App\FileSystem\FileHashGenerator;
use App\FileSystem\Persistence\PersistedFileFactory;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File as SFFile;

class FileContext implements Context
{
    use AssertionTrait;
    use KernelAwareTrait;

    private const TEMP_FILENAME = 'test.tmp';
    private readonly IriConverterInterface $iriConverter;

    private readonly EntityManagerInterface $entityManager;

    public function __construct(IriConverterInterface $iriConverter, EntityManagerInterface $entityManager)
    {
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
    }

    /**
     * @Then there should be no file matching :pattern in upload directory
     */
    public function thereShouldBeNoFileMatchingInUploadDirectory(string $pattern)
    {
        $dirname = $this->getUploadDirname();
        $this->assertEmpty(glob($dirname.'/'.$pattern), \sprintf('There is at least a file matching "%s" in upload directory.', $dirname.'/'.$pattern));
    }

    /**
     * @Then there should be a file matching :pattern in upload directory
     */
    public function thereShouldBeAFileMatchingInUploadDirectory(string $pattern)
    {
        $dirname = $this->getUploadDirname();
        $this->assertNotEmpty(glob($dirname.'/'.$pattern), \sprintf('There is no file matching "%s" in upload directory.', $dirname.'/'.$pattern));
    }

    /**
     * @Then I delete all the files created during test
     */
    public function IDeleteAllTheFilesCreatedDuringTest()
    {
        $finder = new Finder();
        $finder->ignoreUnreadableDirs(true);
        $filter = static fn (SplFileInfo $file) => 0 === $file->getOwner() && 0 === $file->getGroup();
        $fs = new Filesystem();
        try {
            $fs->remove($finder->files()->in($this->getUploadDirname())->filter($filter));
        } catch (IOException $e) {
            // DO nothing
        }
    }

    /**
     * @Then I create a file :className from path :path
     * @Then I have a file :className from path :path for :iri
     */
    public function ICreateAFileFromPath(string $className, string $path, ?string $iri = null)
    {
        $sfFile = new SFFile($path);

        $fs = new Filesystem();
        $fs->copy($sfFile->getRealPath(), $this->getUploadDirname().'/'.$sfFile->getFilename(), true);

        $fileFactory = new PersistedFileFactory(new FileHashGenerator());
        $copiedFile = new SFFile($this->getUploadDirname().'/'.$sfFile->getFilename());

        $file = $fileFactory->create($copiedFile, new $className())
            ->setFilePath($sfFile->getFilename())
            ->setExtension($copiedFile->getExtension())
        ;

        $this->entityManager->persist($file);
        if (null !== $iri) {
            $object = $this->iriConverter->getResourceFromIri($iri);
            $object->addFile($file);
            $this->entityManager->persist($object);
        }
        $this->entityManager->flush();
    }

    /**
     * Perform a TRUNCATE table on all file tables.
     *
     * @BeforeScenario @resetFileTable
     */
    public function resetFileTable(): void
    {
        // Get all metadata classes of File and children.
        $classesMetaData = [];
        $classMetadataFactory = $this->entityManager->getMetadataFactory();
        $classMetadata = $classMetadataFactory->getMetadataFor(File::class);
        foreach ($classMetadata->discriminatorMap as $classes) {
            $classesMetaData[] = $classMetadataFactory->getMetadataFor($classes);
        }
        $classesMetaData[] = $classMetadata;

        // Truncate file tables.
        $this->entityManager->getConnection()->executeQuery('SET foreign_key_checks = 0');
        foreach ($classesMetaData as $classMetaData) {
            $truncateQuery = $this
                ->entityManager
                ->getConnection()
                ->getDatabasePlatform()
                ->getTruncateTableSql($classMetaData->getTableName())
            ;
            $this->entityManager->getConnection()->executeQuery($truncateQuery);
        }
        $this->entityManager->getConnection()->executeQuery('SET foreign_key_checks = 1');
    }

    /**
     * @Then the :format file should have :expected columns
     */
    public function theFileShouldHaveColumns(string $format, int $expected)
    {
        $columnIndex = array_combine(range('A', 'Z'), range(1, 26));
        $spreadsheet = $this->getSpreadSheet($format);
        $columnName = $spreadsheet->getActiveSheet()->getCellCollection()->getHighestColumn();
        $actual = (mb_strlen($columnName) - 1) * 26 + $columnIndex[mb_substr($columnName, -1, 1)];
        $message = \sprintf("Actual number of columns is '%d', but expected '%d'", $actual, $expected);
        $this->deleteSpreadSheet();
        $this->assertSame($expected, $actual, $message);
    }

    /**
     * @Then the :format file should have :expected lines
     */
    public function theFileShouldHaveLines(string $format, int $expected)
    {
        $spreadsheet = $this->getSpreadSheet($format);
        $actual = $spreadsheet->getActiveSheet()->getCellCollection()->getHighestRow();
        $message = \sprintf("Actual number of lines is '%d', but expected '%d'", $actual, $expected);
        $this->deleteSpreadSheet();
        $this->assertSame($expected, $actual, $message);
    }

    /**
     * @Then the :format cell :range should be equal to :expected
     */
    public function theContentAtShouldBeEqualTo(string $format, string $range, $expected)
    {
        $spreadsheet = $this->getSpreadSheet($format);
        $actual = (string) $spreadsheet->getActiveSheet()->getCell($range)->getValue();
        $message = \sprintf("Actual response is '%s', but expected '%s'", $actual, $expected);
        $this->deleteSpreadSheet();
        $this->assertSame($expected, $actual, $message);
    }

    /**
     * @Then the :format file headers are:
     */
    public function theFileHeadersAre(string $format, TableNode $data)
    {
        $worksheet = $this->getSpreadSheet($format)->getActiveSheet();

        foreach ($data->getRow(0) as $key => $header) {
            $columnName = $this->getColumnName($key);
            $actual = (string) $worksheet->getCell($columnName.'1')->getValue();
            $message = \sprintf("Actual header in column '%s' is '%s', but expected '%s'", $columnName, $actual, $header);
            $this->assertSame($header, $actual, $message);
        }
        $this->deleteSpreadSheet();
        $this->theFileShouldHaveColumns($format, \count($data->getRow(0)));
    }

    private function getUploadDirname(): string
    {
        return mb_rtrim($this->getContainer()->getParameter('legacy.upload_dir'), '/');
    }

    private function getSpreadSheet(string $format): Spreadsheet
    {
        $path = $this->kernel->getCacheDir().'/'.self::TEMP_FILENAME;
        file_put_contents($path, $this->getDriver()->getContent());
        $className = 'PhpOffice\\PhpSpreadsheet\\Reader\\'.ucfirst($format);
        $reader = new $className();
        // Advise the Reader that we only want to load cell data
        $reader->setReadDataOnly(true);

        return $reader->load($path);
    }

    /**
     * Retrieve the Column name until ZZ from a zero based index.
     */
    private function getColumnName(int $key): string
    {
        $columnIndex = range('A', 'Z');
        $columnName = '';
        if (0 !== ($overflow = (int) floor($key / 26))) {
            $columnName .= $columnIndex[$overflow - 1];
        }

        return $columnName.$columnIndex[$key % 26];
    }

    private function deleteSpreadSheet()
    {
        unlink($this->kernel->getCacheDir().'/'.self::TEMP_FILENAME);
    }
}

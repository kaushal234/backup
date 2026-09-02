<?php

declare(strict_types=1);

namespace App\Tests\FileSystem\Persistence;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualificationFile;
use App\Entity\User;
use App\FileSystem\Persistence\ContextProviders\FileAdapterContextProviderInterface;
use App\FileSystem\Persistence\FileAdapter;
use App\FileSystem\Persistence\PersistedFileFactory;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;

class FileAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testFileIsAttached()
    {
        $file = new File(__DIR__.'/../../fixtures/file.zip');
        $persistedFileProphecy = $this->prophesize(FirstArticleQualificationFile::class);
        $subject = new FirstArticleQualification();
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $persistedFileFactoryProphecy = $this->prophesize(PersistedFileFactory::class);
        $securityProphecy = $this->prophesize(Security::class);
        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $fileAdapterContextProviderProphecy = $this->prophesize(FileAdapterContextProviderInterface::class);

        $fileAdapterContextProviderProphecy->getGeneratedFilename($subject, ['filename' => 'file.zip', 'createdAt' => null, 'nameLength' => 4, 'extension' => 'zip', 'description' => null])->shouldBeCalledTimes(1)->willReturn($newFilename = 'TESTtoto.zip');
        $fileAdapterContextProviderProphecy->getFileConstraint($subject)->shouldBeCalledTimes(1)->willReturn(null);
        $fileAdapterContextProviderProphecy->processFile($file)->shouldBeCalledTimes(1);

        $serviceLocatorProphecy->get(PersistedFileFactory::class)->shouldBeCalledTimes(1)->willReturn($persistedFileFactoryProphecy->reveal());
        $persistedFileFactoryProphecy->create($file, new FirstArticleQualificationFile())->shouldBeCalledTimes(1)->willReturn($persistedFileProphecy->reveal());
        $fileAdapterContextProviderProphecy->getDirectory()->shouldBeCalledTimes(1)->willReturn($directory = '/tests');
        $persistedFileProphecy->setFilePath($directory.'/'.(new \DateTime())->format('Y/m').'/'.$newFilename)->shouldBeCalledTimes(1);
        $persistedFileProphecy->setExtension('zip')->shouldBeCalledTimes(1);
        $persistedFileProphecy->setStatus('LIVE')->shouldBeCalledTimes(1);
        $persistedFileProphecy->setDescription(null)->shouldBeCalledTimes(1);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willreturn($user = new User());
        $persistedFileProphecy->setPoster($user)->shouldBeCalledTimes(1);

        $fileAdapterContextProviderProphecy->getFileProperty()->shouldBeCalledTimes(2)->willReturn($fileProperty = 'files');
        $serviceLocatorProphecy->get(PropertyAccessorInterface::class)->shouldBeCalledTimes(1)->willReturn($propertyAccessorProphecy->reveal());
        $propertyAccessorProphecy->getValue($subject, $fileProperty)->shouldBeCalledTimes(1)->willreturn(new ArrayCollection());

        $propertyAccessorProphecy->setValue($subject, $fileProperty, [$persistedFileProphecy])->shouldBeCalledTimes(1);

        $contextProvider = new class($fileAdapterContextProviderProphecy->reveal()) implements FileAdapterContextProviderInterface {
            private readonly FileAdapterContextProviderInterface $decorated;

            public function __construct(FileAdapterContextProviderInterface $decorated)
            {
                $this->decorated = $decorated;
            }

            /**
             * {@inheritdoc}
             */
            public function processFile(File $file, array $metadata = [])
            {
                $this->decorated->processFile($file);
            }

            public static function getClass(): string
            {
                return FirstArticleQualificationFile::class;
            }

            public function getFileConstraint($subject): ?Constraint
            {
                return $this->decorated->getFileConstraint($subject);
            }

            public function getFileProperty(): string
            {
                return $this->decorated->getFileProperty();
            }

            public function getDirectory(): string
            {
                return $this->decorated->getDirectory();
            }

            public function getGeneratedFilename(object $subject, array $context): string
            {
                return $this->decorated->getGeneratedFilename($subject, $context);
            }
        };

        $fileAdapter = new FileAdapter($contextProvider, $serviceLocatorProphecy->reveal());

        $fileAdapter->attach($subject, $file);
    }
}

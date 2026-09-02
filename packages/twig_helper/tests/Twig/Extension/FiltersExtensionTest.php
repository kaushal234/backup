<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Tests\Twig\Extension;

use Alvest\TwigHelper\Twig\Extension\FiltersExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class FiltersExtensionTest extends TestCase
{
    public function testApplyFilterWithEmptyFiltersWillReturnValue(): void
    {
        /** @var Environment|MockObject $twigEnvironmentMock */
        $twigEnvironmentMock = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twigEnvironmentMock->expects(self::never())->method('getCache');
        /** @var Filesystem $filesystemMock */
        $filesystemMock = $this->getMockBuilder(Filesystem::class)->getMock();

        $filtersExtension = new FiltersExtension($filesystemMock);
        self::assertSame('value 1', $filtersExtension->applyFilters($twigEnvironmentMock, 'value 1', 0));
        self::assertSame('value 2', $filtersExtension->applyFilters($twigEnvironmentMock, 'value 2', ''));
        self::assertSame('value 3', $filtersExtension->applyFilters($twigEnvironmentMock, 'value 3', null));
        self::assertSame('value 4', $filtersExtension->applyFilters($twigEnvironmentMock, 'value 4', []));
    }

    public function testApplyFilterWillCreateATemplateFile(): void
    {
        $fs = new Filesystem();
        $fs->mkdir('app/cache/test/twig/apply_filter');

        /** @var Environment|MockObject $twigEnvironmentMock */
        $twigEnvironmentMock = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $twigLoaderInterfaceMock = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $twigEnvironmentMock
            ->expects(self::once())
            ->method('getCache')
            ->with(true)
            ->willReturn('app/cache/test/twig');
        $twigEnvironmentMock->expects(self::once())
            ->method('getLoader')
            ->willReturn($twigLoaderInterfaceMock);
        $twigEnvironmentMock->expects(self::exactly(2))
            ->method('setLoader')
            ->withConsecutive([$this->isInstanceOf(LoaderInterface::class)], [$twigLoaderInterfaceMock]);

        $twigEnvironmentMock->expects(self::once())
            ->method('render')
            ->with("localizeddate('medium', 'medium', null, 'EuropeParis').html.twig", ['value' => '2018-01-01'])
            ->willReturn('Yolo')
        ;

        $filePath = "app/cache/test/twig/apply_filter/localizeddate('medium', 'medium', null, 'EuropeParis').html.twig";
        /** @var Filesystem|MockObject $filesystemMock */
        $filesystemMock = $this->getMockBuilder(Filesystem::class)->getMock();
        $filesystemMock
            ->expects(self::once())
            ->method('exists')
            ->with($filePath)
            ->willReturn(false);

        $filesystemMock
            ->expects(self::once())
            ->method('dumpFile')
            ->with($filePath, "{{ value|localizeddate('medium', 'medium', null, 'Europe/Paris') }}")
        ;
        $filtersExtension = new FiltersExtension($filesystemMock);
        self::assertSame('Yolo', $filtersExtension->applyFilters($twigEnvironmentMock, '2018-01-01', "localizeddate('medium', 'medium', null, 'Europe/Paris')"));
    }

    public function testApplyFilterWillUseCachedTemplate(): void
    {
        $fs = new Filesystem();
        $fs->mkdir('app/cache/test/twig/apply_filter');

        /** @var Environment|MockObject $twigEnvironmentMock */
        $twigEnvironmentMock = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $twigLoaderInterfaceMock = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $twigEnvironmentMock
            ->expects(self::once())
            ->method('getCache')
            ->with(true)
            ->willReturn('app/cache/test/twig');
        $twigEnvironmentMock->expects(self::once())
            ->method('getLoader')
            ->willReturn($twigLoaderInterfaceMock);
        $twigEnvironmentMock->expects(self::exactly(2))
            ->method('setLoader')
            ->withConsecutive([$this->isInstanceOf(LoaderInterface::class)], [$twigLoaderInterfaceMock]);

        $twigEnvironmentMock->expects(self::once())
            ->method('render')
            ->with('EuropeParis.html.twig', ['value' => '2018-01-01'])
            ->willReturn('Yolo')
        ;

        $filePath = 'app/cache/test/twig/apply_filter/EuropeParis.html.twig';
        /** @var Filesystem|MockObject $filesystemMock */
        $filesystemMock = $this->getMockBuilder(Filesystem::class)->getMock();
        $filesystemMock
            ->expects(self::once())
            ->method('exists')
            ->with($filePath)
            ->willReturn(true);

        $filesystemMock
            ->expects(self::never())
            ->method('dumpFile')
        ;
        $filtersExtension = new FiltersExtension($filesystemMock);
        self::assertSame('Yolo', $filtersExtension->applyFilters($twigEnvironmentMock, '2018-01-01', 'Europe/Paris'));
    }
}

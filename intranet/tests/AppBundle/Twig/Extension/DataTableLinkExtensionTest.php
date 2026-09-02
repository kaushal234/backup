<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DataTableLinkExtensionTest extends KernelTestCase
{
    private UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $this->urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
    }

    public function testWithoutFilter()
    {
        $twigExtension = new DataTableLinkExtension($this->urlGenerator);

        $route = $twigExtension->datatableLink('technician_on_calls_home', 'technician_on_call');

        self::assertSame('/service/technician-on-calls', $route);
    }

    public function testFilter()
    {
        $twigExtension = new DataTableLinkExtension($this->urlGenerator);

        $route = $twigExtension->datatableLink('technician_on_calls_home', 'technician_on_call', [
            'field1' => 'value',
            'field2' => ['value2', 'value3'],
        ]);

        self::assertSame(
            '/service/technician-on-calls?filter_technician_on_call%5Bfield1%5D%5Bvalue%5D=value&filter_technician_on_call%5Bfield2%5D%5Bvalue%5D%5B0%5D=value2&filter_technician_on_call%5Bfield2%5D%5Bvalue%5D%5B1%5D=value3',
            $route
        );
    }

    public function testSort()
    {
        $twigExtension = new DataTableLinkExtension($this->urlGenerator);

        $route = $twigExtension->datatableLink('technician_on_calls_home', 'technician_on_call', [], [
            'field1' => 'asc',
            'field2' => 'desc',
        ]);

        self::assertSame(
            '/service/technician-on-calls?sort_technician_on_call%5Bfield1%5D=asc&sort_technician_on_call%5Bfield2%5D=desc',
            $route
        );
    }

    public function testFilterAndSort()
    {
        $twigExtension = new DataTableLinkExtension($this->urlGenerator);

        $route = $twigExtension->datatableLink('technician_on_calls_home', 'technician_on_call', [
            'field1' => 'value',
            'field2' => ['value2', 'value3'],
        ], [
            'field1' => 'asc',
            'field2' => 'desc',
        ]);

        self::assertSame(
            '/service/technician-on-calls?filter_technician_on_call%5Bfield1%5D%5Bvalue%5D=value&filter_technician_on_call%5Bfield2%5D%5Bvalue%5D%5B0%5D=value2&filter_technician_on_call%5Bfield2%5D%5Bvalue%5D%5B1%5D=value3&sort_technician_on_call%5Bfield1%5D=asc&sort_technician_on_call%5Bfield2%5D=desc',
            $route
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataTable\Exporter\Type;

use App\DataTable\Exporter\Type\XlsxExporterType;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Exporter\ExporterInterface;
use Kreyu\Bundle\DataTableBundle\Exporter\Type\ExporterType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @group unit
 */
class XlsxExporterTypeTest extends TestCase
{
    public function testConfigureOptionsResolvesWithTheXlsxMimeTypeAsDefaultFormat(): void
    {
        $options = $this->resolveOptions([]);

        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $options['format']);
        $this->assertSame([], $options['extra_query_parameters']);
    }

    public function testExportThrowsBecauseTheApiBuildsTheFileNotThisType(): void
    {
        $type = new XlsxExporterType();

        $this->expectException(\LogicException::class);

        $type->export($this->createMock(DataTableView::class), $this->createMock(ExporterInterface::class), 'export');
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        (new ExporterType())->configureOptions($resolver);
        (new XlsxExporterType())->configureOptions($resolver);

        return $resolver->resolve($options);
    }
}

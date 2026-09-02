<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataTable\Exporter\Type;

use App\DataTable\Exporter\Type\CsvExporterType;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Exporter\ExporterInterface;
use Kreyu\Bundle\DataTableBundle\Exporter\Type\ExporterType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @group unit
 */
class CsvExporterTypeTest extends TestCase
{
    public function testConfigureOptionsResolvesWithTheCsvMimeTypeAsDefaultFormat(): void
    {
        $options = $this->resolveOptions([]);

        $this->assertSame('text/csv', $options['format']);
        $this->assertSame([], $options['extra_query_parameters']);
    }

    public function testConfigureOptionsAcceptsExtraQueryParameters(): void
    {
        $options = $this->resolveOptions(['extra_query_parameters' => ['columns' => 'id,title']]);

        $this->assertSame(['columns' => 'id,title'], $options['extra_query_parameters']);
    }

    public function testExportThrowsBecauseTheApiBuildsTheFileNotThisType(): void
    {
        $type = new CsvExporterType();

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
        // Replicates Kreyu\Bundle\DataTableBundle\Exporter\Type\ResolvedExporterType::getOptionsResolver(),
        // which clones the parent type's already-resolved OptionsResolver before configuring this type's own
        // options on top of it — the exact chain that surfaced the "option does not exist" ordering bug.
        $resolver = new OptionsResolver();
        (new ExporterType())->configureOptions($resolver);
        (new CsvExporterType())->configureOptions($resolver);

        return $resolver->resolve($options);
    }
}

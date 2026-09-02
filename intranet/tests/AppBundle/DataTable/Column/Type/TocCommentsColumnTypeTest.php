<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Column\Type;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\TocCommentsColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TocCommentsColumnTypeTest extends ColumnTypeTestCase
{
    protected ?TranslatorInterface $translator = null;
    protected ?UrlGeneratorInterface $urlGenerator = null;

    public function testDefaults()
    {
        $this->urlGenerator = $this->createUrlGenerator();
        $this->urlGenerator->method('generate')
            ->with('technician_on_calls_show', ['id' => 42])
            ->willReturn('/service/technician_on_calls/42/show');

        $column = $this->createColumn([]);

        $columnValueView = $this->createColumnValueView($column, rowData: new ApiData([
            '@id' => '/service/technician_on_calls/42',
        ]));

        $this->assertSame(
            'bundles/KreyuDataTableBundle/column/icon_remote_preview.html.twig',
            $columnValueView->vars['template_path']
        );

        $vars = $columnValueView->vars['template_vars'];

        $this->assertSame('mingcute:comment-line', $vars['icon']);
        $this->assertSame(['width' => '18', 'height' => '18'], $vars['iconAttr']);
        $this->assertSame('comments', $vars['resource']);
        $this->assertSame(
            ['resource' => '/service/technician_on_calls/42', 'itemsPerPage' => 5],
            $vars['query']
        );
        $this->assertSame(['createdAt' => 'desc'], $vars['order']);
        $this->assertSame('service/technician_on_call/partial/_comments_preview.html.twig', $vars['template']);
        $this->assertSame('/service/technician_on_calls/42/show', $vars['href']);
    }

    public function testTitleIsTranslatedWithTheCorrectKeyDomainAndPlaceholder()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects($this->once())
            ->method('trans')
            ->with(
                'toc.fields.recent_comments_title',
                ['%id%' => 42],
                'technician_on_call'
            )
            ->willReturn('Last comments on TOC#42');

        $column = $this->createColumn([]);

        $columnValueView = $this->createColumnValueView($column, rowData: new ApiData([
            '@id' => '/service/technician_on_calls/42',
        ]));

        $this->assertSame('Last comments on TOC#42', $columnValueView->vars['template_vars']['title']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new TocCommentsColumnType(
            translator: $this->translator ?? $this->createTranslator(),
            urlGenerator: $this->urlGenerator ?? $this->createUrlGenerator(),
        );
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new TemplateColumnType(),
            new ColumnType(),
        ];
    }

    protected function createTranslator(): MockObject&TranslatorInterface
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        return $translator;
    }

    protected function createUrlGenerator(): MockObject&UrlGeneratorInterface
    {
        return $this->createMock(UrlGeneratorInterface::class);
    }
}

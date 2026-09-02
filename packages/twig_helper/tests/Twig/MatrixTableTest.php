<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Tests\Twig;

use Alvest\TwigHelper\Twig\Extension\FiltersExtension;
use Alvest\TwigHelper\Twig\Extension\MatrixExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Template;

use function sprintf;

class MatrixTableTest extends KernelTestCase
{
    public const TEMPLATE = <<<'EOF'
        <html>
            <body>
                {% embed 'matrix_table_layout.html.twig' with config %}
                    {% block table_class %}table matrix-table tooltip-container{% endblock %}
                    {% block card %}
                        {{ block('table') }}
                    {% endblock %}
                {% endembed %}
            </body>
        </html>
    EOF;

    /**
     * @var Template|null
     */
    private $template;

    /**
     * @var array
     */
    private $defaultConfig = [];

    private Environment $twig;

    protected function setUp(): void
    {
        $loader = new FilesystemLoader();

        $loader->addPath(__DIR__.'/../../templates/');
        $loader->addPath(__DIR__.'/../../templates/', 'AlvestTwigHelper');

        $this->twig = new Environment($loader, [
            'cache' => false,
        ]);

        $locator = new FileLocator([__DIR__.'/../../config/tests/']);
        $yamlLoader = new \Symfony\Component\Routing\Loader\YamlFileLoader($locator);
        $routes = $yamlLoader->load('routes.yaml');
        $urlGenerator = new UrlGenerator($routes, new RequestContext());
        $this->twig->addExtension(new RoutingExtension($urlGenerator));
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', [
            'alvest.twig_helper.table.no_results' => 'No results found...',
            'alvest.twig_helper.table.totals' => 'Totals',
        ], 'en');
        $this->twig->addExtension(new TranslationExtension($translator));
        $this->twig->addExtension(new MatrixExtension());
        $this->twig->addExtension(new FiltersExtension(new Filesystem()));

        $this->template = $this->twig->createTemplate(self::TEMPLATE);

        $this->defaultConfig = [
            'data' => [
                'xTotals' => [
                    'LINE 1' => 60,
                    'LINE 2' => 150,
                    'LINE 3' => 240,
                ],
                'yTotals' => [
                    'COLUMN 1' => 120,
                    'COLUMN 2' => 150,
                    'COLUMN 3' => 180,
                ],
                'total' => 450,
                'rows' => [
                    'LINE 1' => [
                        'COLUMN 1' => [
                            'x' => 'LINE 1',
                            'y' => 'COLUMN 1',
                            'value' => 10,
                        ],
                        'COLUMN 2' => [
                            'x' => 'LINE 1',
                            'y' => 'COLUMN 2',
                            'value' => 20,
                        ],
                        'COLUMN 3' => [
                            'x' => 'LINE 1',
                            'y' => 'COLUMN 3',
                            'value' => 30,
                        ],
                    ],
                    'LINE 2' => [
                        'COLUMN 1' => [
                            'x' => 'LINE 2',
                            'y' => 'COLUMN 1',
                            'value' => 40,
                        ],
                        'COLUMN 2' => [
                            'x' => 'LINE 2',
                            'y' => 'COLUMN 2',
                            'value' => 50,
                        ],
                        'COLUMN 3' => [
                            'x' => 'LINE 2',
                            'y' => 'COLUMN 3',
                            'value' => 60,
                        ],
                    ],
                    'LINE 3' => [
                        'COLUMN 1' => [
                            'x' => 'LINE 3',
                            'y' => 'COLUMN 1',
                            'value' => 70,
                        ],
                        'COLUMN 2' => [
                            'x' => 'LINE 3',
                            'y' => 'COLUMN 2',
                            'value' => 80,
                        ],
                        'COLUMN 3' => [
                            'x' => 'LINE 3',
                            'y' => 'COLUMN 3',
                            'value' => 90,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        $this->template = null;
        $this->defaultConfig = [];
        parent::tearDown();
    }

    public function testEmptyDataDisplayNoResult(): void
    {
        $crawler = $this->render([
            'report' => [],
        ]);

        self::assertSame('<p>No results found...</p>', trim($crawler->filter('body')->html()));
    }

    public function testDefaultTableHeader(): void
    {
        $crawler = $this->render($this->defaultConfig);
        self::assertSame('table-responsive ', $crawler->filter('div.table-responsive')->attr('class'));
        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(1, $thead = $table->filter('thead'));
        self::assertCount(1, $header = $thead->filter('tr'));
        self::assertCount(5, $header->filter('th'));
        self::assertEmpty($header->filter('th')->first()->html());
        self::assertEmpty($header->filter('th')->first()->attr('class'));
        self::assertSame('<div><span>COLUMN 1</span></div>', $header->filter('th')->eq(1)->html());
        self::assertSame('top-label', $header->filter('th')->eq(1)->attr('class'));
        self::assertSame('<div><span>COLUMN 2</span></div>', $header->filter('th')->eq(2)->html());
        self::assertSame('<div><span>COLUMN 3</span></div>', $header->filter('th')->eq(3)->html());
        self::assertSame('<div><span>Totals</span></div>', $header->filter('th')->last()->html());
        self::assertStringContainsString('th-total', $header->filter('th')->last()->attr('class'));
        self::assertStringContainsString('top-label', $header->filter('th')->last()->attr('class'));
    }

    public function testDefaultTableBody(): void
    {
        $crawler = $this->render($this->defaultConfig);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(1, $table->filter('tbody'));
        self::assertCount(3, $table->filter('tbody tr'));
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertSame("LINE $i", $line->filter('th')->html());
            self::assertCount(4, $line->filter('td'));
            self::assertSame(10 + ($i - 1) * 30, (int) $line->filter('td')->eq(0)->html());
            self::assertSame(20 + ($i - 1) * 30, (int) $line->filter('td')->eq(1)->html());
            self::assertSame(30 + ($i - 1) * 30, (int) $line->filter('td')->eq(2)->html());
            self::assertSame(30 * (2 + 3 * ($i - 1)), (int) $line->filter('td')->eq(3)->html());
            self::assertStringContainsString('total', $line->filter('td')->last()->attr('class'));
        }
    }

    public function testDefaultTableBodyWithoutZeroShouldBeTheSame(): void
    {
        $crawler = $this->render($this->defaultConfig + ['showZero' => false]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(1, $table->filter('tbody'));
        self::assertCount(3, $table->filter('tbody tr'));
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertSame("LINE $i", $line->filter('th')->html());
            self::assertCount(4, $line->filter('td'));
            self::assertSame(10 + ($i - 1) * 30, (int) $line->filter('td')->eq(0)->html());
            self::assertSame(20 + ($i - 1) * 30, (int) $line->filter('td')->eq(1)->html());
            self::assertSame(30 + ($i - 1) * 30, (int) $line->filter('td')->eq(2)->html());
            self::assertSame(30 * (2 + 3 * ($i - 1)), (int) $line->filter('td')->eq(3)->html());
            self::assertStringContainsString('total', $line->filter('td')->last()->attr('class'));
        }
    }

    public function testDefaultTableBodyWithoutZeroShouldHideZeros(): void
    {
        $config = $this->defaultConfig;
        $config['data']['rows']['LINE 1']['COLUMN 2']['value'] = 0;

        $crawler = $this->render($config + ['showZero' => false]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(1, $table->filter('tbody'));
        self::assertCount(3, $table->filter('tbody tr'));
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertSame("LINE $i", $line->filter('th')->html());
            self::assertCount(4, $line->filter('td'));
            self::assertSame(10 + ($i - 1) * 30, (int) $line->filter('td')->eq(0)->html());
            if (1 === $i) {
                self::assertSame('', $line->filter('td')->eq(1)->html());
            } else {
                self::assertSame(20 + ($i - 1) * 30, (int) $line->filter('td')->eq(1)->html());
            }
            self::assertSame(30 + ($i - 1) * 30, (int) $line->filter('td')->eq(2)->html());
            self::assertSame(30 * (2 + 3 * ($i - 1)), (int) $line->filter('td')->eq(3)->html());
            self::assertStringContainsString('total', $line->filter('td')->last()->attr('class'));
        }
    }

    public function testDefaultTableFooter(): void
    {
        $crawler = $this->render($this->defaultConfig);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(1, $tfoot = $table->filter('tfoot'));
        self::assertCount(1, $footer = $tfoot->filter('tr'));
        self::assertSame('Totals', $footer->filter('th')->first()->html());
        for ($i = 0; $i < 3; ++$i) {
            self::assertSame(120 + $i * 30, (int) $footer->filter('td')->eq($i)->html());
            self::assertStringContainsString('total', $footer->filter('td')->eq($i)->attr('class'));
        }
        self::assertSame(450, (int) $footer->filter('td')->eq(3)->html());
        self::assertStringContainsString('total', $footer->filter('td')->eq(3)->attr('class'));
    }

    public function testLinkOption(): void
    {
        $link = [
            'link' => [
                'route' => 'test_route_simple',
                'xParam' => 'xval',
                'yParam' => 'yval',
            ],
        ];

        $crawler = $this->render($this->defaultConfig + $link);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertSame("LINE $i", $line->filter('th')->html());
            self::assertCount(4, $line->filter('td'));
            self::assertSame(10 + ($i - 1) * 30, (int) $line->filter('td a')->eq(0)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=COLUMN%201", $line->filter('td a')->eq(0)->attr('href'));
            self::assertSame(20 + ($i - 1) * 30, (int) $line->filter('td a')->eq(1)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=COLUMN%202", $line->filter('td a')->eq(1)->attr('href'));
            self::assertSame(30 + ($i - 1) * 30, (int) $line->filter('td a')->eq(2)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=COLUMN%203", $line->filter('td a')->eq(2)->attr('href'));
            self::assertSame(30 * (2 + 3 * ($i - 1)), (int) $line->filter('td a')->eq(3)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=", $line->filter('td a')->eq(3)->attr('href'));
        }
        $footer = $table->filter('tfoot tr');
        for ($i = 1; $i < 4; ++$i) {
            self::assertSame(120 + ($i - 1) * 30, (int) $footer->filter('td a')->eq($i - 1)->html());
            self::assertSame("/meow?xval=&yval=COLUMN%20$i", $footer->filter('td a')->eq($i - 1)->attr('href'));
        }
        self::assertSame(450, (int) $footer->filter('td a')->eq(3)->html());
        self::assertSame('/meow?xval=&yval=', $footer->filter('td a')->eq(3)->attr('href'));
    }

    public function testLinkOptionWithIris(): void
    {
        $link = [
            'link' => [
                'route' => 'test_route_simple',
                'xParam' => 'xval',
                'yParam' => 'yval',
            ],
        ];

        $config = $this->defaultConfig;
        $config['data']['metadata']['xIris'] = [
            'LINE 3' => '/lines/3',
        ];
        $config['data']['metadata']['yIris'] = [
            'COLUMN 1' => '/columns/1',
            'COLUMN 2' => '/columns/2',
            'COLUMN 3' => '/columns/3',
        ];

        $crawler = $this->render($config + $link);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        for ($i = 0; $i < 2; ++$i) {
            $line = $table->filter('tbody tr')->eq($i);
            $lineNumber = $i + 1;
            self::assertSame("LINE $lineNumber", $line->filter('th')->html());
            self::assertCount(4, $line->filter('td'));
            self::assertSame(10 + $i * 30, (int) $line->filter('td a')->eq(0)->html());
            self::assertSame("/meow?xval=LINE%20$lineNumber&yval=/columns/1", $line->filter('td a')->eq(0)->attr('href'));
            self::assertSame(20 + $i * 30, (int) $line->filter('td a')->eq(1)->html());
            self::assertSame("/meow?xval=LINE%20$lineNumber&yval=/columns/2", $line->filter('td a')->eq(1)->attr('href'));
            self::assertSame(30 + $i * 30, (int) $line->filter('td a')->eq(2)->html());
            self::assertSame("/meow?xval=LINE%20$lineNumber&yval=/columns/3", $line->filter('td a')->eq(2)->attr('href'));
            self::assertSame(30 * (2 + 3 * $i), (int) $line->filter('td a')->eq(3)->html());
            self::assertSame("/meow?xval=LINE%20$lineNumber&yval=", $line->filter('td a')->eq(3)->attr('href'));
        }

        $line = $table->filter('tbody tr')->eq(2);
        self::assertSame('LINE 3', $line->filter('th')->html());
        self::assertCount(4, $line->filter('td'));
        self::assertSame(70, (int) $line->filter('td a')->eq(0)->html());
        self::assertSame('/meow?xval=/lines/3&yval=/columns/1', $line->filter('td a')->eq(0)->attr('href'));
        self::assertSame(80, (int) $line->filter('td a')->eq(1)->html());
        self::assertSame('/meow?xval=/lines/3&yval=/columns/2', $line->filter('td a')->eq(1)->attr('href'));
        self::assertSame(90, (int) $line->filter('td a')->eq(2)->html());
        self::assertSame('/meow?xval=/lines/3&yval=/columns/3', $line->filter('td a')->eq(2)->attr('href'));
        self::assertSame(240, (int) $line->filter('td a')->eq(3)->html());
        self::assertSame('/meow?xval=/lines/3&yval=', $line->filter('td a')->eq(3)->attr('href'));

        $footer = $table->filter('tfoot tr');
        for ($i = 1; $i < 4; ++$i) {
            self::assertSame(120 + ($i - 1) * 30, (int) $footer->filter('td a')->eq($i - 1)->html());
            self::assertSame("/meow?xval=&yval=/columns/$i", $footer->filter('td a')->eq($i - 1)->attr('href'));
        }
        self::assertSame(450, (int) $footer->filter('td a')->eq(3)->html());
        self::assertSame('/meow?xval=&yval=', $footer->filter('td a')->eq(3)->attr('href'));
    }

    public function testLinkOptionWithAdditionalParams(): void
    {
        $link = [
            'link' => [
                'route' => 'test_route_simple',
                'xParam' => 'xval',
                'yParam' => 'yval',
                'additionalParams' => ['pim' => 'paf'],
            ],
        ];

        $crawler = $this->render($this->defaultConfig + $link);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertSame("LINE $i", $line->filter('th')->html());
            self::assertCount(4, $line->filter('td'));
            self::assertSame(10 + ($i - 1) * 30, (int) $line->filter('td a')->eq(0)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=COLUMN%201&pim=paf", $line->filter('td a')->eq(0)->attr('href'));
            self::assertSame(20 + ($i - 1) * 30, (int) $line->filter('td a')->eq(1)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=COLUMN%202&pim=paf", $line->filter('td a')->eq(1)->attr('href'));
            self::assertSame(30 + ($i - 1) * 30, (int) $line->filter('td a')->eq(2)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=COLUMN%203&pim=paf", $line->filter('td a')->eq(2)->attr('href'));
            self::assertSame(30 * (2 + 3 * ($i - 1)), (int) $line->filter('td a')->eq(3)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=&pim=paf", $line->filter('td a')->eq(3)->attr('href'));
        }
        $footer = $table->filter('tfoot tr');
        for ($i = 1; $i < 4; ++$i) {
            self::assertSame(120 + ($i - 1) * 30, (int) $footer->filter('td a')->eq($i - 1)->html());
            self::assertSame("/meow?xval=&yval=COLUMN%20$i&pim=paf", $footer->filter('td a')->eq($i - 1)->attr('href'));
        }
        self::assertSame(450, (int) $footer->filter('td a')->eq(3)->html());
        self::assertSame('/meow?xval=&yval=&pim=paf', $footer->filter('td a')->eq(3)->attr('href'));
    }

    public function testLinkOptionWithAdditionalParamsOnly(): void
    {
        $link = [
            'link' => [
                'route' => 'test_route_simple',
                'xParam' => null,
                'yParam' => null,
                'additionalParams' => ['pim' => 'paf'],
                'removeXTotalsParam' => true,
                'removeYTotalsParam' => true,
            ],
        ];

        $crawler = $this->render($this->defaultConfig + $link);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        foreach (range(0, 2) as $i) {
            $line = $table->filter('tbody tr')->eq($i);
            foreach (range(0, 3) as $j) {
                self::assertSame('/meow?pim=paf', $line->filter('td a')->eq($j)->attr('href'));
            }
        }
        $footer = $table->filter('tfoot tr');
        foreach (range(0, 3) as $i) {
            self::assertSame('/meow?pim=paf', $footer->filter('td a')->eq($i)->attr('href'));
        }
    }

    public function testLinkOptionWithAdditionalParamsCanOverrideGivenParameter(): void
    {
        $link = [
            'link' => [
                'route' => 'test_route_simple',
                'xParam' => 'xval',
                'yParam' => 'yval',
                'additionalParams' => ['xval' => 'foo', 'yval' => 'bar'],
            ],
        ];

        $crawler = $this->render($this->defaultConfig + $link);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        for ($column = 1; $column < 4; ++$column) {
            $line = $table->filter('tbody tr')->eq($column - 1);
            for ($c = 1; $c < 4; ++$c) {
                self::assertSame('/meow?xval=foo&yval=bar', $line->filter('td a')->eq($column - 1)->attr('href'));
            }
        }
        $footer = $table->filter('tfoot tr');
        for ($column = 1; $column < 4; ++$column) {
            self::assertSame('/meow?xval=foo&yval=bar', $footer->filter('td a')->eq($column - 1)->attr('href'));
        }
        self::assertSame('/meow?xval=foo&yval=bar', $footer->filter('td a')->eq(3)->attr('href'));
    }

    public function testHideXTotals(): void
    {
        $crawler = $this->render($this->defaultConfig + ['hideTotals' => 'x']);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(4, $table->filter('thead tr th'));
        // test each line
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertCount(3, $line->filter('td'));
        }

        self::assertCount(3, $table->filter('tfoot tr td'));
    }

    public function testHideYTotals(): void
    {
        $crawler = $this->render($this->defaultConfig + ['hideTotals' => 'y']);
        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(5, $table->filter('thead tr th'));
        // test each line
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertCount(4, $line->filter('td'));
        }

        self::assertEmpty($table->filter('tfoot'));
    }

    public function testHideBothTotals(): void
    {
        $crawler = $this->render($this->defaultConfig + ['hideTotals' => 'both']);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(4, $table->filter('thead tr th'));
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertCount(3, $line->filter('td'));
        }
        self::assertEmpty($table->filter('tfoot'));
    }

    public function testOrderingYAxisAffectsHeaders(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 2', 'COLUMN 3', 'COLUMN 1']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(5, $header = $table->filter('thead tr th'));
        self::assertSame('<div><span>COLUMN 2</span></div>', $header->filter('th')->eq(1)->html());
        self::assertSame('<div><span>COLUMN 3</span></div>', $header->filter('th')->eq(2)->html());
        self::assertSame('<div><span>COLUMN 1</span></div>', $header->filter('th')->eq(3)->html());
    }

    public function testOrderingYAxisAffectsValues(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 2', 'COLUMN 3', 'COLUMN 1']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(3, $table->filter('tbody tr'));
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertSame("LINE $i", $line->filter('th')->html());
            self::assertCount(4, $line->filter('td'));
            self::assertSame(20 + ($i - 1) * 30, (int) $line->filter('td')->eq(0)->html());
            self::assertSame(30 + ($i - 1) * 30, (int) $line->filter('td')->eq(1)->html());
            self::assertSame(10 + ($i - 1) * 30, (int) $line->filter('td')->eq(2)->html());
            self::assertSame(30 * (2 + 3 * ($i - 1)), (int) $line->filter('td')->eq(3)->html());
            self::assertStringContainsString('total', $line->filter('td')->last()->attr('class'));
        }
    }

    public function testOrderingYAxisAffectsTotals(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 2', 'COLUMN 3', 'COLUMN 1']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(4, $footer = $table->filter('tfoot tr td'));
        self::assertSame(150, (int) $footer->filter('td')->eq(0)->html());
        self::assertSame(180, (int) $footer->filter('td')->eq(1)->html());
        self::assertSame(120, (int) $footer->filter('td')->eq(2)->html());
        self::assertSame(450, (int) $footer->filter('td')->eq(3)->html());
    }

    public function testFilteringYAxisAffectsHeaders(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 2', 'COLUMN 3']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(4, $header = $table->filter('thead tr th'));
        self::assertSame('<div><span>COLUMN 2</span></div>', $header->filter('th')->eq(1)->html());
        self::assertSame('<div><span>COLUMN 3</span></div>', $header->filter('th')->eq(2)->html());
    }

    public function testFilteringYAxisAffectsValues(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 2', 'COLUMN 3']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(3, $table->filter('tbody tr'));
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertSame("LINE $i", $line->filter('th')->html());
            self::assertCount(3, $line->filter('td'));
            self::assertSame(20 + ($i - 1) * 30, (int) (int) $line->filter('td')->eq(0)->html());
            self::assertSame(30 + ($i - 1) * 30, (int) (int) $line->filter('td')->eq(1)->html());
            self::assertSame((60 * $i) - 10, (int) (int) $line->filter('td')->eq(2)->html());
            self::assertStringContainsString('total', $line->filter('td')->last()->attr('class'));
        }
    }

    public function testFilteringYAxisAffectsTotals(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 2', 'COLUMN 3']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(3, $footer = $table->filter('tfoot tr td'));
        self::assertSame(150, (int) $footer->filter('td')->eq(0)->html());
        self::assertSame(180, (int) $footer->filter('td')->eq(1)->html());
        self::assertSame(330, (int) $footer->filter('td')->eq(2)->html());
    }

    public function testFilteringWithNonExistingColumnYAxisAffectsHeaders(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 4', 'COLUMN 3']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(4, $header = $table->filter('thead tr th'));
        self::assertSame('<div><span>COLUMN 4</span></div>', $header->filter('th')->eq(1)->html());
        self::assertSame('<div><span>COLUMN 3</span></div>', $header->filter('th')->eq(2)->html());
    }

    public function testFilteringWithNonExistingColumnYAxisAffectsValues(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 4', 'COLUMN 3']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(3, $table->filter('tbody tr'));
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertSame("LINE $i", $line->filter('th')->html());
            self::assertCount(3, $line->filter('td'));
            self::assertSame(0, (int) $line->filter('td')->eq(0)->html());
            self::assertSame(30 + ($i - 1) * 30, (int) $line->filter('td')->eq(1)->html());
            self::assertSame(30 + ($i - 1) * 30, (int) $line->filter('td')->eq(2)->html());
            self::assertStringContainsString('total', $line->filter('td')->last()->attr('class'));
        }
    }

    public function testFilteringWithNonExistingColumnYAxisAffectsTotals(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 4', 'COLUMN 3']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(3, $footer = $table->filter('tfoot tr td'));
        self::assertSame(0, (int) $footer->filter('td')->eq(0)->html());
        self::assertSame(180, (int) $footer->filter('td')->eq(1)->html());
        self::assertSame(180, (int) $footer->filter('td')->eq(2)->html());
    }

    public function testOrderingXAxisAffectsHeaders(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['lines' => ['LINE 2', 'LINE 3', 'LINE 1']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(5, $header = $table->filter('thead tr th'));
        self::assertSame('<div><span>COLUMN 1</span></div>', $header->filter('th')->eq(1)->html());
        self::assertSame('<div><span>COLUMN 2</span></div>', $header->filter('th')->eq(2)->html());
        self::assertSame('<div><span>COLUMN 3</span></div>', $header->filter('th')->eq(3)->html());
    }

    public function testOrderingXAxisAffectsValues(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['lines' => ['LINE 2', 'LINE 3', 'LINE 1']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(3, $table->filter('tbody tr'));
        foreach ([2, 3, 1] as $j => $i) {
            $line = $table->filter('tbody tr')->eq($j);
            self::assertSame("LINE $i", $line->filter('th')->html());
            self::assertCount(4, $line->filter('td'));
            self::assertSame(10 + ($i - 1) * 30, (int) $line->filter('td')->eq(0)->html());
            self::assertSame(20 + ($i - 1) * 30, (int) $line->filter('td')->eq(1)->html());
            self::assertSame(30 + ($i - 1) * 30, (int) $line->filter('td')->eq(2)->html());
            self::assertSame(30 * (2 + 3 * ($i - 1)), (int) $line->filter('td')->eq(3)->html());
            self::assertStringContainsString('total', $line->filter('td')->last()->attr('class'));
        }
    }

    public function testOrderingXAxisDoesntAffectsTotals(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['lines' => ['LINE 2', 'LINE 3', 'LINE 1']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(4, $footer = $table->filter('tfoot tr td'));
        self::assertSame(120, (int) $footer->filter('td')->eq(0)->html());
        self::assertSame(150, (int) $footer->filter('td')->eq(1)->html());
        self::assertSame(180, (int) $footer->filter('td')->eq(2)->html());
        self::assertSame(450, (int) $footer->filter('td')->eq(3)->html());
    }

    public function testFilteringXAxisAffectsHeaders(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['lines' => ['LINE 2', 'LINE 3']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(5, $header = $table->filter('thead tr th'));
        self::assertSame('<div><span>COLUMN 1</span></div>', $header->filter('th')->eq(1)->html());
        self::assertSame('<div><span>COLUMN 2</span></div>', $header->filter('th')->eq(2)->html());
        self::assertSame('<div><span>COLUMN 3</span></div>', $header->filter('th')->eq(3)->html());
    }

    public function testFilteringXAxisAffectsValues(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['lines' => ['LINE 2', 'LINE 3']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(2, $table->filter('tbody tr'));
        foreach ([2, 3] as $j => $i) {
            $line = $table->filter('tbody tr')->eq($j);
            self::assertSame("LINE $i", $line->filter('th')->html());
            self::assertCount(4, $line->filter('td'));
            self::assertSame(10 + ($i - 1) * 30, (int) $line->filter('td')->eq(0)->html());
            self::assertSame(20 + ($i - 1) * 30, (int) $line->filter('td')->eq(1)->html());
            self::assertSame(30 + ($i - 1) * 30, (int) $line->filter('td')->eq(2)->html());
            self::assertSame(30 * (2 + 3 * ($i - 1)), (int) (int) $line->filter('td')->eq(3)->html());
            self::assertStringContainsString('total', $line->filter('td')->last()->attr('class'));
        }
    }

    public function testFilteringXAxisAffectsTotals(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['lines' => ['LINE 2', 'LINE 3']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(4, $footer = $table->filter('tfoot tr td'));
        self::assertSame(110, (int) $footer->filter('td')->eq(0)->html());
        self::assertSame(130, (int) $footer->filter('td')->eq(1)->html());
        self::assertSame(150, (int) $footer->filter('td')->eq(2)->html());
        self::assertSame(390, (int) $footer->filter('td')->eq(3)->html());
    }

    public function testFilteringXAndYAxisAffectsHeaders(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 3', 'COLUMN 1'], 'lines' => ['LINE 3', 'LINE 1']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(4, $header = $table->filter('thead tr th'));
        self::assertSame('<div><span>COLUMN 3</span></div>', $header->filter('th')->eq(1)->html());
        self::assertSame('<div><span>COLUMN 1</span></div>', $header->filter('th')->eq(2)->html());
    }

    public function testFilteringXAndYAxisAffectsValues(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 3', 'COLUMN 1'], 'lines' => ['LINE 3', 'LINE 1']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(2, $table->filter('tbody tr'));
        foreach ([3, 1] as $j => $i) {
            $line = $table->filter('tbody tr')->eq($j);
            self::assertSame("LINE $i", $line->filter('th')->html());
            self::assertCount(3, $line->filter('td'));
            self::assertSame(30 + ($i - 1) * 30, (int) $line->filter('td')->eq(0)->html());
            self::assertSame(10 + ($i - 1) * 30, (int) $line->filter('td')->eq(1)->html());
            self::assertSame(40 + ($i - 1) * 60, (int) $line->filter('td')->eq(2)->html());
            self::assertStringContainsString('total', $line->filter('td')->last()->attr('class'));
        }
    }

    public function testFilteringXAndYAxisAffectsTotals(): void
    {
        $crawler = $this->render($this->defaultConfig + ['headers' => ['columns' => ['COLUMN 3', 'COLUMN 1'], 'lines' => ['LINE 3', 'LINE 1']]]);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        self::assertCount(3, $footer = $table->filter('tfoot tr td'));
        self::assertSame(120, (int) $footer->filter('td')->eq(0)->html());
        self::assertSame(80, (int) $footer->filter('td')->eq(1)->html());
        self::assertSame(200, (int) $footer->filter('td')->eq(2)->html());
    }

    public function testColumnHeaderWithStringFilters(): void
    {
        $crawler = $this->render($this->defaultConfig + ['filters' => ['columnHeaders' => "replace({' ': '_'})|lower"]]);
        $header = $crawler->filter('div.table-responsive table.table.matrix-table thead tr');
        self::assertCount(5, $header->filter('th'));
        self::assertSame('<div><span>column_1</span></div>', $header->filter('th')->eq(1)->html());
        self::assertSame('<div><span>column_2</span></div>', $header->filter('th')->eq(2)->html());
        self::assertSame('<div><span>column_3</span></div>', $header->filter('th')->eq(3)->html());
        self::assertSame('<div><span>Totals</span></div>', $header->filter('th')->eq(4)->html());
    }

    public function testColumnHeaderWithArrayFilters(): void
    {
        $crawler = $this->render($this->defaultConfig + ['filters' => ['columnHeaders' => ["replace({' ': '_'})", 'lower']]]);
        $header = $crawler->filter('div.table-responsive table.table.matrix-table thead tr');
        self::assertCount(5, $header->filter('th'));
        self::assertSame('<div><span>column_1</span></div>', $header->filter('th')->eq(1)->html());
        self::assertSame('<div><span>column_2</span></div>', $header->filter('th')->eq(2)->html());
        self::assertSame('<div><span>column_3</span></div>', $header->filter('th')->eq(3)->html());
        self::assertSame('<div><span>Totals</span></div>', $header->filter('th')->eq(4)->html());
    }

    public function testLineHeaderWithStringFilters(): void
    {
        $crawler = $this->render($this->defaultConfig + ['filters' => ['lineHeaders' => "replace({' ': '_'})|lower"]]);
        $lines = $crawler->filter('div.table-responsive table.table.matrix-table tbody tr');
        self::assertSame('line_1', $lines->eq(0)->filter('th')->html());
        self::assertSame('line_2', $lines->eq(1)->filter('th')->html());
        self::assertSame('line_3', $lines->eq(2)->filter('th')->html());
    }

    public function testLineHeaderWithArrayFilters(): void
    {
        $crawler = $this->render($this->defaultConfig + ['filters' => ['lineHeaders' => ["replace({' ': '_'})", 'lower']]]);
        $lines = $crawler->filter('div.table-responsive table.table.matrix-table tbody tr');
        self::assertSame('line_1', $lines->eq(0)->filter('th')->html());
        self::assertSame('line_2', $lines->eq(1)->filter('th')->html());
        self::assertSame('line_3', $lines->eq(2)->filter('th')->html());
    }

    public function testValuesWithStringFilters(): void
    {
        $crawler = $this->render($this->defaultConfig + ['filters' => ['values' => 'number_format(2)']]);
        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertSame(sprintf('%.2f', 10 + ($i - 1) * 30), $line->filter('td')->eq(0)->html());
            self::assertSame(sprintf('%.2f', 20 + ($i - 1) * 30), $line->filter('td')->eq(1)->html());
            self::assertSame(sprintf('%.2f', 30 + ($i - 1) * 30), $line->filter('td')->eq(2)->html());
            self::assertSame(sprintf('%.2f', 30 * (2 + 3 * ($i - 1))), $line->filter('td')->eq(3)->html());
        }
        $footer = $table->filter('tfoot tr td');
        for ($i = 0; $i < 3; ++$i) {
            self::assertSame(sprintf('%.2f', 120 + $i * 30), $footer->filter('td')->eq($i)->html());
        }
        self::assertSame('450.00', $footer->filter('td')->eq(3)->html());
    }

    public function testLinksWithStringFilters(): void
    {
        $config = [
            'link' => [
                'route' => 'test_route_simple',
                'xParam' => 'xval',
                'yParam' => 'yval',
            ],
            'filters' => ['values' => 'number_format(2)'],
        ];

        $crawler = $this->render($this->defaultConfig + $config);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertSame(sprintf('%.2f', 10 + ($i - 1) * 30), $line->filter('td a')->eq(0)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=COLUMN%201", $line->filter('td a')->eq(0)->attr('href'));
            self::assertSame(sprintf('%.2f', 20 + ($i - 1) * 30), $line->filter('td a')->eq(1)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=COLUMN%202", $line->filter('td a')->eq(1)->attr('href'));
            self::assertSame(sprintf('%.2f', 30 + ($i - 1) * 30), $line->filter('td a')->eq(2)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=COLUMN%203", $line->filter('td a')->eq(2)->attr('href'));
            self::assertSame(sprintf('%.2f', 30 * (2 + 3 * ($i - 1))), $line->filter('td a')->eq(3)->html());
            self::assertSame("/meow?xval=LINE%20$i&yval=", $line->filter('td a')->eq(3)->attr('href'));
        }
        $footer = $table->filter('tfoot tr');
        for ($i = 1; $i < 4; ++$i) {
            self::assertSame(sprintf('%.2f', 120 + ($i - 1) * 30), $footer->filter('td a')->eq($i - 1)->html());
            self::assertSame("/meow?xval=&yval=COLUMN%20$i", $footer->filter('td a')->eq($i - 1)->attr('href'));
        }
        self::assertSame('450.00', $footer->filter('td a')->eq(3)->html());
        self::assertSame('/meow?xval=&yval=', $footer->filter('td a')->eq(3)->attr('href'));
    }

    public function testLinksWithNorXNeitherYParam(): void
    {
        $config = [
            'link' => [
                'route' => 'test_route_simple',
                'xParam' => 'xval',
                'yParam' => 'yval',
            ],
            'removeXTotalsParam' => true,
            'removeYTotalsParam' => true,
        ];

        $crawler = $this->render($this->defaultConfig + $config);

        $table = $crawler->filter('div.table-responsive table.table.matrix-table');
        for ($i = 1; $i < 4; ++$i) {
            $line = $table->filter('tbody tr')->eq($i - 1);
            self::assertSame("/meow?xval=LINE%20$i", $line->filter('td a')->eq(3)->attr('href'));
        }
        $footer = $table->filter('tfoot tr');
        for ($i = 1; $i < 4; ++$i) {
            self::assertSame("/meow?yval=COLUMN%20$i", $footer->filter('td a')->eq($i - 1)->attr('href'));
        }
        self::assertSame('/meow', $footer->filter('td a')->eq(3)->attr('href'));
    }

    public function testOptionTemplate(): void
    {
        $config = $this->defaultConfig;
        $config['template'] = '_tests/_included_template_matrix.html.twig';
        $config['additionalParams'] = ['extraParam' => 'coucou'];
        $crawler = $this->render($config);

        self::assertSame(
            '<p>10 coucou</p>',
            trim($crawler->filter('div.table-responsive table.table.matrix-table')->eq(0)->filter('td')->html())
        );
    }

    public function testOptionContainerClass(): void
    {
        $config = $this->defaultConfig;
        $config['containerClass'] = 'table-rotated-headers';
        $crawler = $this->render($config);

        self::assertSame('table-responsive table-rotated-headers', $crawler->filter('div.table-responsive')->attr('class'));
    }

    private function render(array $config): Crawler
    {
        $render = $this->template->render([
            'config' => $config,
        ]);

        return new Crawler($render);
    }
}

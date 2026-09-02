<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Tests\Twig;

use Alvest\TwigHelper\Twig\Extension\ConcatExtension;
use Alvest\TwigHelper\Twig\Extension\DateFormatExtension;
use Alvest\TwigHelper\Twig\Extension\ExternalUrlExtension;
use Alvest\TwigHelper\Twig\Extension\FiltersExtension;
use Alvest\TwigHelper\Twig\Extension\NestedPropertiesExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Template;

use function count;

use const PHP_VERSION;

class ReportTableTest extends KernelTestCase
{
    public const TEMPLATE = <<<'EOF'
        <html>
            <body>
                {% embed 'report_table_layout.html.twig' with {reportTable: config} %}
                    {% block card_body_class %}ibox-content{% endblock %}
                    {% block table_class %}report-table{% endblock %}
                    {% block card_header %}
                        <div class="ibox-title">
                            <h5>{{ table.title|raw }}</h5>
                        </div>
                    {% endblock card_header %}
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
        ], 'en');
        $this->twig->addExtension(new TranslationExtension($translator));
        $this->twig->addExtension(new ConcatExtension());
        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new NestedPropertiesExtension(PropertyAccess::createPropertyAccessor()));
        $this->twig->addExtension(new FiltersExtension(new Filesystem()));
        $this->twig->addExtension(new ExternalUrlExtension($urlGenerator, new RequestContext()));
        $this->twig->addExtension(new DateFormatExtension());

        $this->template = $this->twig->createTemplate(self::TEMPLATE);

        $this->defaultConfig = [
            'items' => [
                'column1' => [
                    'label' => 'COLUMN 1',
                ],
                'column2' => [
                    'label' => 'COLUMN 2',
                ],
            ],
            'title' => 'TEST DISPLAY DATA',
            'data' => [
                [
                    'column1' => 'VALUE 1 LINE 1',
                    'column2' => 'VALUE 2 LINE 1',
                ],
                [
                    'column1' => 'VALUE 1 LINE 2',
                    'column2' => 'VALUE 2 LINE 2',
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

    public function testTitleIsDisplayed(): void
    {
        $crawler = $this->render([
            'title' => 'TEST TITLE',
            'data' => [],
        ]);

        self::assertSame('TEST TITLE', $crawler->filter('.ibox-title h5')->text(null, true));
    }

    public function testEmptyDataDisplayNoResult(): void
    {
        $crawler = $this->render([
            'title' => 'TEST DISPLAY NO RESULT',
            'data' => [],
        ]);

        self::assertSame('<p>No results found...</p>', trim($crawler->filter('.ibox-content')->html()));
    }

    public function testColumnLabelsAndValues(): void
    {
        $crawler = $this->render($this->defaultConfig);

        self::assertSame('COLUMN 1', trim($crawler->filter('.ibox-content table thead tr th')->first()->text(null, true)));
        self::assertSame('COLUMN 2', trim($crawler->filter('.ibox-content table thead tr th')->last()->text(null, true)));

        $lines = $crawler->filter('.ibox-content table tbody tr');

        self::assertSame('VALUE 1 LINE 1', trim($lines->first()->filter('td')->first()->text(null, true)));
        self::assertSame('VALUE 2 LINE 1', trim($lines->first()->filter('td')->last()->text(null, true)));

        self::assertSame('VALUE 1 LINE 2', trim($lines->last()->filter('td')->first()->text(null, true)));
        self::assertSame('VALUE 2 LINE 2', trim($lines->last()->filter('td')->last()->text(null, true)));

        self::assertSame((string) count($this->defaultConfig['items']), trim($crawler->filter('.ibox-content table tfoot tr td')->first()->attr('colspan')));
    }

    public function testDefaultValues(): void
    {
        $crawler = $this->render($this->defaultConfig);

        self::assertCount(1, $crawler->filter(".ibox-content > .search-table input[id^='filter-']"));
        self::assertSame('#filter-TESTDISPLAYDATA', $crawler->filter('.ibox-content table')->attr('data-filter'), 'The attribute must contain the name of title without spaces.');

        self::assertCount(1, $crawler->filter('.ibox-content table tfoot ul.pagination'));
        self::assertSame('20', $crawler->filter('.ibox-content table')->attr('data-page-size'));

        self::assertSame('true', $crawler->filter('.ibox-content table thead th')->first()->attr('data-sort-initial'));
        self::assertSame('numeric', $crawler->filter('.ibox-content table thead th')->first()->attr('data-type'));
    }

    public function testDisableSearch(): void
    {
        $config = $this->defaultConfig + ['search' => false];
        $crawler = $this->render($config);

        self::assertCount(0, $crawler->filter(".ibox-content > input[id^='filter-']"));
        self::assertNull($crawler->filter('.ibox-content table')->attr('data-filter'));
    }

    public function testDisablePagination(): void
    {
        $config = $this->defaultConfig + ['pagination' => false];
        $crawler = $this->render($config);

        self::assertCount(1, $crawler->filter('.ibox-content table tfoot ul.pagination'));
        self::assertSame('9999999', $crawler->filter('.ibox-content table')->attr('data-page-size'));
        self::assertSame('true', $crawler->filter('.ibox-content table')->attr('data-do-not-paginate'));
    }

    public function testDisableTotalNumber(): void
    {
        $config = $this->defaultConfig + ['totalNumber' => false];
        $crawler = $this->render($config);

        self::assertCount(0, $crawler->filter('tfoot'));
    }

    public function testSetPaginationValue(): void
    {
        $config = $this->defaultConfig + ['pagination' => 50];
        $crawler = $this->render($config);

        self::assertSame('50', $crawler->filter('.ibox-content table')->attr('data-page-size'));
    }

    public function testInitialSorting(): void
    {
        $config = $this->defaultConfig + ['initialSorting' => 'column2'];
        $crawler = $this->render($config);

        self::assertNull($crawler->filter('.ibox-content table thead th')->eq(0)->attr('data-sort-initial'));
        self::assertSame('true', $crawler->filter('.ibox-content table thead th')->eq(1)->attr('data-sort-initial'));
    }

    public function testOptionSortableToFalse(): void
    {
        $config = $this->defaultConfig;
        $config['items']['column2']['sortable'] = false;
        $crawler = $this->render($config);

        self::assertSame('true', $crawler->filter('.ibox-content table thead th')->eq(1)->attr('data-sort-ignore'));
    }

    public function testOptionSortableToNumeric(): void
    {
        $config = $this->defaultConfig;
        $config['items']['column2']['sortable'] = 'numeric';
        $crawler = $this->render($config);

        self::assertSame('numeric', $crawler->filter('.ibox-content table thead th')->eq(1)->attr('data-type'));
    }

    public function testOptionClass(): void
    {
        $config = $this->defaultConfig;
        $config['items']['column2']['class'] = 'my-custom-class';
        $crawler = $this->render($config);

        self::assertSame('my-custom-class', $crawler->filter('.ibox-content table thead th')->eq(1)->attr('class'));

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                'my-custom-class',
                $crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->attr('class')
            );
        }
    }

    public function testOptionFilters(): void
    {
        $config = $this->defaultConfig;
        $config['items']['column2']['filters'] = 'lower';
        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                'VALUE 1 LINE '.($i + 1),
                trim($crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(0)->html())
            );
            self::assertSame(
                'value 2 line '.($i + 1),
                trim($crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->html())
            );
        }
    }

    public function testOptionTemplate(): void
    {
        $config = $this->defaultConfig;
        $config['items']['column2']['template'] = '_tests/_included_template.html.twig';
        $config['items']['column2']['templateParams'] = ['extraParam' => 'hello'];
        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                'VALUE 1 LINE '.($i + 1),
                trim($crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(0)->html())
            );
            self::assertSame(
                '<p>VALUE 1 LINE '.($i + 1).' VALUE 2 LINE '.($i + 1).' COLUMN 2 column2 hello</p>',
                trim($crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->html())
            );
        }
    }

    public function testFiltersCanPreserveNullValuesClass(): void
    {
        $config = $this->defaultConfig;
        $config['items']['column2']['filters'] = 'localizeddate';
        $config['items']['column2']['preserveNull'] = true;
        $config['data'][0]['column2'] = date('2017-06-01');
        $config['data'][1]['column2'] = null;

        $crawler = $this->render($config);

        self::assertSame(
            PHP_VERSION >= '8.1.21' ? "Jun 1, 2017, 12:00:00\u{202f}AM" : 'Jun 1, 2017, 12:00:00 AM',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(0)->filter('td')->eq(1)->html())
        );
        self::assertSame(
            '',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(1)->filter('td')->eq(1)->html())
        );
    }

    public function testFiltersCanPreserveNullValuesOnMultiplePropertiesClass(): void
    {
        $config = $this->defaultConfig;
        foreach ($config['data'] as $i => &$data) {
            $data['multiple'] = [
                'prop1' => null,
                'prop2' => null,
            ];
            $data['single'] = [
                'prop' => null,
            ];
        }
        unset($data);
        $config['items']['multiple'] = [
            'label' => 'COLUMN OBJECT',
            'properties' => ['multiple.prop1', 'multiple.prop2'],
            'filters' => 'localizeddate',
            'preserveNull' => true,
        ];
        $config['items']['single'] = [
            'label' => 'COLUMN OBJECT',
            'properties' => ['single.prop'],
            'filters' => 'localizeddate',
            'preserveNull' => true,
        ];

        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                '',
                trim($crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(2)->html())
            );
            self::assertSame(
                '',
                trim($crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(3)->html())
            );
        }
    }

    public function testOptionProperties(): void
    {
        $config = $this->defaultConfig;
        foreach ($config['data'] as $i => &$data) {
            $data['multiple'] = [
                'prop1' => "prop 1 value line $i",
                'prop2' => "prop 2 value line $i",
            ];
            $data['single'] = [
                'prop' => "single prop #$i",
            ];
        }
        unset($data);
        $config['items']['multiple'] = [
            'label' => 'COLUMN OBJECT',
            'properties' => ['multiple.prop1', 'multiple.prop2'],
        ];
        $config['items']['single'] = [
            'label' => 'COLUMN OBJECT',
            'properties' => ['single.prop'],
        ];
        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                html_entity_decode("prop 1 value line $i&nbsp;prop 2 value line $i"),
                trim($crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(2)->html())
            );
            self::assertSame(
                "single prop #$i",
                trim($crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(3)->html())
            );
        }
    }

    public function testOptionBoolean(): void
    {
        $config = $this->defaultConfig;
        foreach ($config['data'] as $i => &$data) {
            $data['boolean'] = (0 === $i % 2);
        }
        unset($data);
        $config['items']['boolean'] = [
            'label' => 'COLUMN BOOLEAN',
            'boolean' => [
                'false' => 'TOO BAD',
                'true' => 'OH YEAH',
            ],
        ];
        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                0 === $i % 2 ? 'OH YEAH' : 'TOO BAD',
                trim($crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(2)->html())
            );
        }
    }

    public function testOptionCollapse(): void
    {
        $config = $this->defaultConfig;
        $config['items']['column2']['collapse'] = true;
        $crawler = $this->render($config);

        self::assertSame('all', $crawler->filter('.ibox-content table thead th')->eq(1)->attr('data-hide'));
    }

    public function testOptionLinkNoParams(): void
    {
        $config = $this->defaultConfig;

        $config['items']['column2']['link'] = [
            'route' => 'test_route_simple',
        ];
        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                '/meow',
                $crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->filter('a')->attr('href')
            );
        }
    }

    public function testOptionLinkSimpleId(): void
    {
        $config = $this->defaultConfig;
        foreach ($config['data'] as $i => &$data) {
            $data['id'] = $i + 1;
        }
        unset($data);
        $config['items']['column2']['link'] = [
            'route' => 'test_route_one_param',
            'params' => [
                'id' => 'id',
            ],
        ];
        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            $id = $i + 1;
            self::assertSame(
                "/kittens/$id",
                $crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->filter('a')->attr('href')
            );
        }
    }

    public function testOptionLinkMultipleParams(): void
    {
        $config = $this->defaultConfig;
        foreach ($config['data'] as $i => &$data) {
            $data['id'] = $i + 1;
            $data['box'] = [
                'reference' => 'BOX_'.$data['id'],
                'manufacturer' => 'Schrödinger',
            ];
        }
        unset($data);
        $config['items']['column2']['link'] = [
            'route' => 'test_route_two_params',
            'params' => [
                'id' => 'id',
                'boxRef' => 'box.reference',
            ],
        ];
        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            $id = $i + 1;
            self::assertSame(
                "/boxes/BOX_$id/kittens/$id",
                $crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->filter('a')->attr('href')
            );
        }
    }

    public function testOptionLinkFixedParams(): void
    {
        $config = $this->defaultConfig;
        foreach ($config['data'] as $i => &$data) {
            $data['id'] = $i + 1;
        }
        unset($data);
        $config['items']['column2']['link'] = [
            'route' => 'test_route_two_params',
            'params' => [
                'id' => 'id',
            ],
            'fixedParams' => [
                'boxRef' => 'always_the_same_box',
            ],
        ];
        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            $id = $i + 1;
            self::assertSame(
                "/boxes/always_the_same_box/kittens/$id",
                $crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->filter('a')->attr('href')
            );
        }
    }

    public function testOptionLinkIriId(): void
    {
        $config = $this->defaultConfig;
        foreach ($config['data'] as $i => &$data) {
            $data['user'] = [
                'id' => $i + 1,
            ];
        }
        unset($data);
        $config['items']['column2']['link'] = [
            'route' => 'test_route_one_param',
            'params' => [
                'id' => 'user.@id',
            ],
        ];
        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            $id = $i + 1;
            self::assertSame(
                "/kittens/$id",
                $crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->filter('a')->attr('href')
            );
        }
    }

    public function testOptionLinkContent(): void
    {
        $config = $this->defaultConfig;
        foreach ($config['data'] as $i => &$data) {
            $data['id'] = $i + 1;
        }
        unset($data);
        $config['items']['column2']['link'] = [
            'route' => 'test_route_one_param',
            'params' => [
                'id' => 'id',
            ],
            'content' => '<span class="btn btn-danger btn-xs"><i class="fa fa-trash fa-fw"></i></span>',
        ];

        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                '<span class="btn btn-danger btn-xs"><i class="fa fa-trash fa-fw"></i></span>',
                trim($crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->filter('a')->html())
            );
        }
    }

    public function testOptionLinkPopup(): void
    {
        $config = $this->defaultConfig;
        foreach ($config['data'] as $i => &$data) {
            $data['id'] = $i + 1;
        }
        unset($data);
        $config['items']['column2']['link'] = [
            'route' => 'test_route_one_param',
            'params' => [
                'id' => 'id',
            ],
            'popup' => [
                'message' => 'Wowowowo stop it and break it down !',
            ],
        ];

        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                "return confirm('Wowowowo stop it and break it down !');",
                $crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->filter('a')->attr('onclick')
            );
        }
    }

    public function testOptionTooltipOnTD(): void
    {
        $config = $this->defaultConfig;

        $config['items']['column2']['tooltip'] = [
            'property' => 'column2',
        ];

        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                'tooltip',
                $crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->attr('data-toggle')
            );
            self::assertSame(
                'top',
                $crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(1)->attr('data-placement')
            );
        }
    }

    public function testOptionTooltipOnTR(): void
    {
        $config = $this->defaultConfig;

        $config['tooltip'] = [
            'property' => 'column2',
        ];

        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                'tooltip',
                $crawler->filter('.ibox-content table tbody')->filter('tr')->eq($i)->attr('data-toggle')
            );
            self::assertSame(
                'top',
                $crawler->filter('.ibox-content table tbody')->filter('tr')->eq($i)->attr('data-placement')
            );
        }
    }

    public function testNestedProperties(): void
    {
        $config = $this->defaultConfig;
        foreach ($config['data'] as $i => &$data) {
            $data['nested'] = [
                'veryNested' => [
                    'deep' => "yo $i",
                ],
            ];
        }
        unset($data);
        $config['items']['nested'] = [
            'label' => 'yo',
            'properties' => ['nested.veryNested.deep'],
        ];

        $crawler = $this->render($config);

        for ($i = 0, $iMax = count($config['data']); $i < $iMax; ++$i) {
            self::assertSame(
                "yo $i",
                trim($crawler->filter('.ibox-content table tbody tr')->eq($i)->filter('td')->eq(2)->html())
            );
        }
    }

    public function testNestedBoolean(): void
    {
        $config = $this->defaultConfig;
        foreach ($config['data'] as $i => &$data) {
            $data['nested'] = [
                'veryNested' => [
                    'deep' => (bool) ($i % 2),
                ],
            ];
        }
        unset($data);
        $config['items']['nested'] = [
            'label' => 'yo',
            'properties' => ['nested.veryNested.deep'],
            'boolean' => [
                'false' => 'Faux !',
                'true' => 'Vrai !',
            ],
        ];
        $crawler = $this->render($config);

        self::assertSame('Faux !', trim($crawler->filter('.ibox-content table tbody tr')->eq(0)->filter('td')->eq(2)->html()));
        self::assertSame('Vrai !', trim($crawler->filter('.ibox-content table tbody tr')->eq(1)->filter('td')->eq(2)->html()));

        // Test default
        unset($config['items']['nested']['boolean']);
        $crawler = $this->render($config);

        self::assertSame('no', trim($crawler->filter('.ibox-content table tbody tr')->eq(0)->filter('td')->eq(2)->html()));
        self::assertSame('yes', trim($crawler->filter('.ibox-content table tbody tr')->eq(1)->filter('td')->eq(2)->html()));
    }

    public function testPHPPagination(): void
    {
        $config = $this->defaultConfig;
        $config['pagination'] = [
            'currentPage' => 5,
            'paginationUrl' => [
                'route' => 'quality_calibrated_tools_index',
                'parameters' => [],
            ],
        ];

        $items = [
            'hydra:totalItems' => 100,
            'hydra:view' => [
                'hydra:next' => 2,
            ],
            'hydra:member' => [],
        ];

        for ($i = 0; $i < 100; ++$i) {
            $items['hydra:member'][] = [
                'column1' => "VALUE 1 LINE $i",
                'column2' => "VALUE 2 LINE $i",
            ];
        }

        $config['data'] = $items;

        $crawler = $this->render($config);

        $crawler->filter('.ibox-content nav ul.pagination li')->each(static function (Crawler $li): void {
            self::assertStringStartsWith(
                '/quality/calibrated-tools',
                $li->filter('a')->attr('href')
            );
        });
    }

    public function testColumnLabelsAreNotEscaped(): void
    {
        $config = $this->defaultConfig;
        $config['items']['column1']['label'] = '<i class="fa fa-fw fa-poop"></i>';
        $config['items']['column2']['collapse'] = true;
        $config['items']['column2']['label'] = '<i class="fa fa-fw fa-brain"></i>';
        $crawler = $this->render($config);

        self::assertSame('<i class="fa fa-fw fa-poop"></i>', trim($crawler->filter('.ibox-content table thead tr th')->first()->html()));
        self::assertSame('<i class="fa fa-fw fa-brain"></i>', trim($crawler->filter('.ibox-content table thead tr th')->eq(1)->html()));
    }

    public function testTitleAreNotEscapedButDoesnTBreakFiltering(): void
    {
        $config = $this->defaultConfig;
        $config['title'] = 'Some Title<i class="fa fa-poop"></i>&nbsp;1984<i class="fa fa-poop"></i>';

        $crawler = $this->render($config);
        self::assertSame('Some Title<i class="fa fa-poop"></i>'.html_entity_decode('&nbsp;').'1984<i class="fa fa-poop"></i>', $crawler->filter('.ibox-title h5')->html());
        self::assertCount(1, $crawler->filter(".ibox-content > .search-table input[id^='filter-']"));
        self::assertSame('#filter-SomeTitle1984', $crawler->filter('.ibox-content table')->attr('data-filter'), 'The attribute must contain the name without html tags.');
    }

    public function testDotsAndSpacesInTitleAreReplaced(): void
    {
        $config = $this->defaultConfig;
        $config['title'] = 'So me.Tit le';
        $crawler = $this->render($config);
        self::assertSame('#filter-SomeTitle', $crawler->filter('.ibox-content table')->attr('data-filter'), 'Special Chars Should be replaced in title');
    }

    /**
     * @return Crawler
     */
    private function render(array $config)
    {
        $render = $this->template->render([
            'config' => $config,
        ]);

        return new Crawler($render);
    }
}

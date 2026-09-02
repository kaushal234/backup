<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Tests\Twig;

use Alvest\TwigHelper\Twig\Extension\ConcatExtension;
use Alvest\TwigHelper\Twig\Extension\DateFormatExtension;
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
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Template;

use const PHP_VERSION;

class AssociationTableTest extends KernelTestCase
{
    public const TEMPLATE = <<<'EOF'
        <html>
            <body>
                {% embed 'association_table_layout.html.twig' with {associationData: config} %}
                    {% block card_body_class %}ibox-content{% endblock %}
                    {% block table_class %}association-table{% endblock %}
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

    /**
     * @var Environment
     */
    private $twig;

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
        $this->twig->addExtension(new TranslationExtension());
        $this->twig->addExtension(new ConcatExtension());
        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new NestedPropertiesExtension(PropertyAccess::createPropertyAccessor()));
        $this->twig->addExtension(new FiltersExtension(new Filesystem()));
        $this->twig->addExtension(new DateFormatExtension());

        $this->template = $this->twig->createTemplate(self::TEMPLATE);

        $this->defaultConfig = [
            'items' => [
                'prop1' => [
                    'label' => 'PROPERTY 1',
                ],
                'prop2' => [
                    'label' => 'PROPERTY 2',
                ],
            ],
            'title' => 'TEST DISPLAY DATA',
            'data' => [
                'prop1' => 'VALUE 1',
                'prop2' => 'VALUE 2',
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
            'items' => [],
            'title' => 'TEST TITLE',
            'data' => [],
        ]);

        self::assertSame('TEST TITLE', $crawler->filter('.ibox-title h5')->html());
    }

    public function testColumnLabelsAndValues(): void
    {
        $crawler = $this->render($this->defaultConfig);

        self::assertCount(1, $crawler->filter('.ibox-content table.association-table'));
        self::assertCount(0, $crawler->filter('.ibox-content table thead'));

        $lines = $crawler->filter('.ibox-content table tbody tr');

        for ($i = 1; $i < 3; ++$i) {
            self::assertSame("PROPERTY $i", trim($lines->eq($i - 1)->filter('th')->html()));
            self::assertSame("VALUE $i", trim($lines->eq($i - 1)->filter('td')->html()));
        }
    }

    public function testGenerateLineForKeysWithoutData(): void
    {
        $config = $this->defaultConfig;

        $config['items']['key_without_data']['label'] = 'label';
        $crawler = $this->render($config);

        self::assertSame('label', trim($crawler->filter('.ibox-content table tbody tr')->eq(2)->filter('th')->html()));
        self::assertSame('', trim($crawler->filter('.ibox-content table tbody tr')->eq(2)->filter('td')->html()));
    }

    public function testOptionClass(): void
    {
        $config = $this->defaultConfig;
        $config['items']['prop2']['class'] = 'my-custom-class';
        $crawler = $this->render($config);

        self::assertSame(
            'my-custom-class',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(1)->filter('td')->attr('class'))
        );
    }

    public function testOptionFilters(): void
    {
        $config = $this->defaultConfig;
        $config['items']['prop2']['filters'] = 'lower|concat(" - added")';
        $crawler = $this->render($config);

        self::assertSame(
            'value 2 - added',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(1)->filter('td')->html())
        );
    }

    public function testFiltersCanPreserveNullValuesClass(): void
    {
        $config = $this->defaultConfig;
        $config['items']['prop1']['filters'] = 'localizeddate';
        $config['items']['prop2']['filters'] = 'localizeddate';
        $config['items']['prop2']['preserveNull'] = true;

        $config['items']['key_without_data'] = [
            'filters' => 'localizeddate',
            'preserveNull' => true,
            'label' => 'label',
        ];
        $config['data']['prop1'] = date('2017-06-01');
        $config['data']['prop2'] = null;

        $crawler = $this->render($config);

        self::assertSame(
            PHP_VERSION >= '8.1.21' ? "Jun 1, 2017, 12:00:00\u{202f}AM" : 'Jun 1, 2017, 12:00:00 AM',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(0)->filter('td')->html())
        );
        self::assertSame(
            '',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(1)->filter('td')->html())
        );
        self::assertSame(
            '',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(2)->filter('td')->html())
        );
    }

    public function testFiltersCanPreserveNullValuesOnMultiplePropertiesClass(): void
    {
        $config = [
            'items' => [
                'multiple' => [
                    'label' => 'PROPERTY 1',
                    'properties' => ['multiple.prop1', 'multiple.prop2'],
                    'filters' => 'localizeddate',
                    'preserveNull' => true,
                ],
                'single' => [
                    'label' => 'PROPERTY 2',
                    'properties' => ['single.prop'],
                    'filters' => 'localizeddate',
                    'preserveNull' => true,
                ],
            ],
            'title' => 'TEST DISPLAY DATA',
            'data' => [
                'multiple' => [
                    'prop1' => null,
                    'prop2' => null,
                ],
                'single' => [
                    'prop' => null,
                ],
            ],
        ];

        $crawler = $this->render($config);

        self::assertSame(
            '',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(0)->filter('td')->html())
        );
        self::assertSame(
            '',
            trim($crawler->filter('')->eq(1)->filter('td')->html())
        );
    }

    public function testOptionProperties(): void
    {
        $config = [
            'items' => [
                'multiple' => [
                    'label' => 'PROPERTY 1',
                    'properties' => ['multiple.prop1', 'multiple.prop2'],
                ],
                'single' => [
                    'label' => 'PROPERTY 2',
                    'properties' => ['single.prop'],
                ],
            ],
            'title' => 'TEST DISPLAY DATA',
            'data' => [
                'multiple' => [
                    'prop1' => 'multiple prop 1',
                    'prop2' => 'multiple prop 2',
                ],
                'single' => [
                    'prop' => 'single prop',
                ],
            ],
        ];
        $crawler = $this->render($config);

        self::assertSame(
            html_entity_decode('multiple prop 1&nbsp;multiple prop 2'),
            trim($crawler->filter('.ibox-content table tbody tr')->eq(0)->filter('td')->html())
        );
        self::assertSame(
            'single prop',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(1)->filter('td')->html())
        );
    }

    public function testDefaultBoolean(): void
    {
        $config = [
            'items' => [
                'prop1' => [
                    'label' => 'PROPERTY 1',
                ],
                'prop2' => [
                    'label' => 'PROPERTY 2',
                ],
            ],
            'title' => 'TEST DISPLAY DATA',
            'data' => [
                'prop1' => false,
                'prop2' => true,
            ],
        ];

        $crawler = $this->render($config);

        self::assertSame(
            'no',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(0)->filter('td')->html())
        );
        self::assertSame(
            'yes',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(1)->filter('td')->html())
        );
    }

    public function testOptionBoolean(): void
    {
        $config = [
            'items' => [
                'prop1' => [
                    'label' => 'PROPERTY 1',
                    'boolean' => [
                        'false' => 'TOO BAD 1',
                        'true' => 'OH YEAH 1',
                    ],
                ],
                'prop2' => [
                    'label' => 'PROPERTY 1',
                    'boolean' => [
                        'false' => 'TOO BAD 2',
                        'true' => 'OH YEAH 2',
                    ],
                ],
            ],
            'title' => 'TEST DISPLAY DATA',
            'data' => [
                'prop1' => false,
                'prop2' => true,
            ],
        ];

        $crawler = $this->render($config);

        self::assertSame(
            'TOO BAD 1',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(0)->filter('td')->html())
        );
        self::assertSame(
            'OH YEAH 2',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(1)->filter('td')->html())
        );
    }

    public function testNestedProperties(): void
    {
        $config = $this->defaultConfig;
        $config['data']['nested'] = [
            'veryNested' => [
                'deep' => 'yo value',
            ],
        ];
        $config['items']['nested'] = [
            'label' => 'yo label',
            'properties' => ['nested.veryNested.deep'],
        ];

        $crawler = $this->render($config);

        $lines = $crawler->filter('.ibox-content table tbody tr');
        self::assertSame('yo label', trim($lines->eq(2)->filter('th')->html()));
        self::assertSame('yo value', trim($lines->eq(2)->filter('td')->html()));
    }

    public function testNestedBoolean(): void
    {
        $config = [
            'items' => [
                'nested' => [
                    'label' => 'red bool',
                    'properties' => ['nested.bool'],
                    'boolean' => [
                        'false' => 'Faux !',
                        'true' => 'Vrai !',
                    ],
                ],
                'nested_default' => [
                    'label' => "J'aime trop ton bool",
                    'properties' => ['nested_default.bool'],
                ],
            ],
            'title' => 'TEST DISPLAY DATA',
            'data' => [
                'nested' => [
                    'bool' => false,
                ],
                'nested_default' => [
                    'bool' => true,
                ],
            ],
        ];

        $crawler = $this->render($config);

        $lines = $crawler->filter('.ibox-content table tbody tr');

        self::assertSame('red bool', trim($lines->eq(0)->filter('th')->html()));
        self::assertSame('Faux !', trim($lines->eq(0)->filter('td')->html()));

        self::assertSame("J'aime trop ton bool", trim($lines->eq(1)->filter('th')->html()));
        self::assertSame('yes', trim($lines->eq(1)->filter('td')->html()));
    }

    public function testOptionLinkNoParams(): void
    {
        $config = $this->defaultConfig;

        $config['items']['prop1']['link'] = [
            'route' => 'test_route_simple',
        ];
        $crawler = $this->render($config);

        self::assertSame(
            '/meow',
            $crawler->filter('.ibox-content table tbody tr td a')->attr('href')
        );
    }

    public function testOptionLinkSimpleId(): void
    {
        $config = $this->defaultConfig;

        $config['items']['prop1']['link'] = [
            'route' => 'test_route_one_param',
            'params' => [
                'id' => 'id',
            ],
        ];

        $config['data']['id'] = 42;
        $crawler = $this->render($config);

        self::assertSame(
            '/kittens/42',
            $crawler->filter('.ibox-content table tbody tr td a')->attr('href')
        );
    }

    public function testOptionLinkMultipleParams(): void
    {
        $config = $this->defaultConfig;

        $config['items']['prop1']['link'] = [
            'route' => 'test_route_two_params',
            'params' => [
                'id' => 'id',
                'boxRef' => 'box.reference',
            ],
        ];

        $config['data']['id'] = 42;
        $config['data']['box'] = [
            'reference' => 'BOX_24',
            'manufacturer' => 'Schrödinger',
        ];

        $crawler = $this->render($config);

        self::assertSame(
            '/boxes/BOX_24/kittens/42',
            $crawler->filter('.ibox-content table tbody tr td a')->attr('href')
        );
    }

    public function testOptionLinkFixedParams(): void
    {
        $config = $this->defaultConfig;

        $config['items']['prop1']['link'] = [
            'route' => 'test_route_two_params',
            'params' => [
                'id' => 'id',
            ],
            'fixedParams' => [
                'boxRef' => 'always_the_same_box',
            ],
        ];

        $config['data']['id'] = 42;

        $crawler = $this->render($config);

        self::assertSame(
            '/boxes/always_the_same_box/kittens/42',
            $crawler->filter('.ibox-content table tbody tr td a')->attr('href')
        );
    }

    public function testOptionLinkIriId(): void
    {
        $config = $this->defaultConfig;

        $config['items']['prop1']['link'] = [
            'route' => 'test_route_one_param',
            'params' => [
                'id' => 'user.@id',
            ],
        ];

        $config['data']['user']['id'] = 42;

        $crawler = $this->render($config);

        self::assertSame(
            '/kittens/42',
            $crawler->filter('.ibox-content table tbody tr td a')->attr('href')
        );
    }

    public function testOptionLinkContent(): void
    {
        $config = $this->defaultConfig;

        $config['items']['prop1']['link'] = [
            'route' => 'test_route_one_param',
            'params' => [
                'id' => 'id',
            ],
            'content' => '<span class="btn btn-danger btn-xs"><i class="fa fa-trash fa-fw"></i></span>',
        ];

        $config['data']['id'] = 42;
        $crawler = $this->render($config);

        self::assertSame(
            '<span class="btn btn-danger btn-xs"><i class="fa fa-trash fa-fw"></i></span>',
            trim($crawler->filter('.ibox-content table tbody tr td a')->html())
        );
    }

    public function testOptionLinkPopup(): void
    {
        $config = $this->defaultConfig;

        $config['items']['prop1']['link'] = [
            'route' => 'test_route_one_param',
            'params' => [
                'id' => 'id',
            ],
            'popup' => [
                'message' => 'Wowowowo stop it and break it down !',
            ],
        ];

        $config['data']['id'] = 42;
        $crawler = $this->render($config);

        self::assertSame(
            "return confirm('Wowowowo stop it and break it down !');",
            $crawler->filter('.ibox-content table tbody tr td a')->attr('onclick')
        );
    }

    public function testOptionTemplate(): void
    {
        $config = $this->defaultConfig;
        $config['items']['prop1']['template'] = '_tests/_included_template_assoc.html.twig';
        $config['items']['prop1']['templateParams'] = ['extraParam' => 'coucou'];
        $crawler = $this->render($config);

        self::assertSame(
            '<p>VALUE 1 VALUE 2 PROPERTY 1 prop1 coucou</p>',
            trim($crawler->filter('.ibox-content table tbody tr')->eq(0)->filter('td')->html())
        );
    }

    public function testOptionHideNull(): void
    {
        $config = [
            'items' => [
                'prop1' => [
                    'label' => 'PROPERTY 1',
                    'hideNull' => true,
                ],
                'prop2' => [
                    'label' => 'PROPERTY 2',
                    'hideNull' => true,
                ],
                'prop3' => [
                    'label' => 'PROPERTY 3',
                    'hideNull' => false,
                ],
                'prop4' => [
                    'label' => 'PROPERTY 4',
                ],
            ],
            'title' => 'TEST DISPLAY DATA',
            'data' => [
                'prop1' => 'VALUE 1',
                'prop2' => null,
                'prop3' => null,
                'prop4' => null,
            ],
        ];

        $crawler = $this->render($config);

        $line = $crawler->filter('.ibox-content table tbody tr')->eq(0);
        self::assertSame('PROPERTY 1', trim($line->filter('th')->html()));
        self::assertSame('VALUE 1', trim($line->filter('td')->html()));

        $line = $crawler->filter('.ibox-content table tbody tr')->eq(1);
        self::assertSame('PROPERTY 3', trim($line->filter('th')->html()));
        self::assertSame('', trim($line->filter('td')->html()));

        $line = $crawler->filter('.ibox-content table tbody tr')->eq(2);
        self::assertSame('PROPERTY 4', trim($line->filter('th')->html()));
        self::assertSame('', trim($line->filter('td')->html()));
    }

    public function testColumnLabelsAreNotEscaped(): void
    {
        $config = $this->defaultConfig;
        $config['items']['prop1']['label'] = '<i class="fa fa-fw fa-poop"></i>';
        $crawler = $this->render($config);

        self::assertSame('<i class="fa fa-fw fa-poop"></i>', $crawler->filter('.ibox-content table tbody tr')->first()->filter('th')->html());
    }

    public function testOptionRowHeaderWidth(): void
    {
        $config = $this->defaultConfig;

        $config['rowHeaderWidth'] = '3.14%';

        $crawler = $this->render($config);

        $lines = $crawler->filter('.ibox-content table tbody tr');
        for ($i = 1; $i < 3; ++$i) {
            self::assertSame('width:3.14%;', $lines->eq($i - 1)->filter('th')->attr('style'));
        }
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

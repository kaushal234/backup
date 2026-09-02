<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Template;

class TabsTest extends KernelTestCase
{
    public const TEMPLATE = <<<'EOF'
        <html>
            <body>
                {% embed 'tabs_layout.html.twig' with {'tabsData': config} %}
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

        $this->template = $this->twig->createTemplate(self::TEMPLATE);

        $this->defaultConfig = [
            'tabs' => [
                'tab1' => [
                    'label' => 'COLUMN 1',
                    'template' => '_tests/_included_template_tabs.html.twig',
                ],
                'tab2' => [
                    'label' => 'COLUMN 2',
                    'defaultTab' => true,
                    'template' => '_tests/_included_template_tabs.html.twig',
                ],
            ],
            'data' => ['foo' => 'bar'],
        ];
    }

    protected function tearDown(): void
    {
        $this->template = null;
        $this->defaultConfig = [];
        parent::tearDown();
    }

    public function testLabelsAndTabs(): void
    {
        $crawler = $this->render($this->defaultConfig);

        self::assertSame('COLUMN 1', trim($crawler->filter('.tabs-container ul li')->first()->text(null, true)));
        self::assertSame('COLUMN 2', trim($crawler->filter('.tabs-container ul li')->last()->text(null, true)));

        $tabs = $crawler->filter('.tabs-container .tab-content');

        self::assertSame('bar', trim($tabs->first()->filter('.tab-pane div')->first()->text(null, true)));
        self::assertSame('bar', trim($tabs->last()->filter('.tab-pane div')->first()->text(null, true)));
    }

    public function testDefaultActiveTab(): void
    {
        $crawler = $this->render($this->defaultConfig);

        $tabs = $crawler->filter('.tabs-container .tab-content > div');
        self::assertSame('tab-pane', $tabs->first()->attr('class'));
        self::assertSame('tab-pane active', $tabs->last()->attr('class'));
    }

    public function testDefaultActiveTabIsChangingWithLink(): void
    {
        $config = $this->defaultConfig;
        $config['link'] = 'tab1';
        $crawler = $this->render($config);

        $tabs = $crawler->filter('.tabs-container .tab-content > div');
        self::assertSame('tab-pane active', $tabs->first()->attr('class'));
        self::assertSame('tab-pane', $tabs->last()->attr('class'));
    }

    public function testBadgePrimaryIsDisplayedWhenDefined(): void
    {
        $config = $this->defaultConfig;
        $config['tabs']['tab1']['badge'] = 1;
        $crawler = $this->render($config);

        self::assertStringContainsString(
            '<span class="badge badge-primary">1</span>',
            $crawler->filter('.tabs-container ul > li')->eq(0)->filter('a')->html()
        );
        self::assertStringNotContainsString(
            '<span class="badge badge-primary">1</span>',
            $crawler->filter('.tabs-container ul > li')->eq(1)->filter('a')->html()
        );
    }

    public function testBadgeDangerIsDisplayedWhenDefined(): void
    {
        $config = $this->defaultConfig;
        $config['tabs']['tab2']['badge'] = 0;
        $crawler = $this->render($config);

        self::assertStringContainsString(
            '<span class="badge badge-danger">0</span>',
            $crawler->filter('.tabs-container ul > li')->eq(1)->filter('a')->html()
        );
        self::assertStringNotContainsString(
            '<span class="badge badge-danger">0</span>',
            $crawler->filter('.tabs-container ul > li')->eq(0)->filter('a')->html()
        );
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

<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Tests\Twig\Extension;

use Alvest\TwigHelper\Twig\Extension\ExternalUrlExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class ExternalUrlExtensionTest extends TestCase
{
    /**
     * @dataProvider getLinks
     */
    public function testLinkGenerationIsWorkingAsExpected($url, $target, $expected): void
    {
        self::assertSame(
            $expected,
            $this->getExtension()->getHtmlLinkFromString($url, $target)
        );
    }

    public function getLinks()
    {
        yield 'valid URL & target blank' => [
            'https://www.google.fr/search?q=lol+cat+fail',
            true,
            '<a href="https://www.google.fr/search?q=lol+cat+fail" target="_blank">www.google.fr</a>',
        ];

        yield 'valid URL & target self' => [
            'https://www.google.fr/search?q=lol+cat+fail',
            false,
            '<a href="https://www.google.fr/search?q=lol+cat+fail">www.google.fr</a>',
        ];

        yield 'invalid URL' => [
            'http:///Time waits for no man, unless that man is Chuck Norris',
            true,
            'http:///Time waits for no man, unless that man is Chuck Norris',
        ];
    }

    private function getExtension()
    {
        /** @var MockObject|UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)->getMock();

        return new ExternalUrlExtension($urlGenerator, new RequestContext());
    }
}

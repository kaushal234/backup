<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Twig\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function sprintf;

use const PHP_URL_HOST;

class ExternalUrlExtension extends AbstractExtension
{
    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var RequestContext
     */
    private $requestContext;

    public function __construct(UrlGeneratorInterface $generator, RequestContext $requestContext)
    {
        $this->generator = $generator;
        $this->requestContext = $requestContext;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('html_link_from_string', [$this, 'getHtmlLinkFromString']),
        ];
    }

    /**
     * @return array<object> An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('full_url', [$this, 'getFullUrl']),
        ];
    }

    /**
     * @param string              $name
     * @param array<string|mixed> $parameters
     *
     * @return string
     */
    public function getFullUrl($name, $parameters = [])
    {
        $baseUrl = $this->requestContext->getBaseUrl();
        $this->requestContext->setBaseUrl('');

        $url = $this->generator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);

        $this->requestContext->setBaseUrl($baseUrl);

        return $url;
    }

    /**
     * @param string $url
     * @param bool   $blank
     *
     * @return string
     */
    public function getHtmlLinkFromString($url, $blank = true)
    {
        if (false === $domain = parse_url($url, PHP_URL_HOST)) {
            return $url;
        }

        return sprintf('<a href="%s"%s>%s</a>', $url, $blank ? ' target="_blank"' : '', $domain);
    }
}

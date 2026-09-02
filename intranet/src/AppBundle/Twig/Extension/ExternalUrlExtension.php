<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ExternalUrlExtension extends AbstractExtension
{
    private readonly UrlGeneratorInterface $generator;

    private readonly RequestContext $requestContext;

    private readonly string $mountPath;

    public function __construct(UrlGeneratorInterface $generator, RequestContext $requestContext, string $mountPath)
    {
        $this->generator = $generator;
        $this->requestContext = $requestContext;
        $this->mountPath = $mountPath;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('html_link_from_string', [$this, 'getHtmlLinkFromString']),
        ];
    }

    /**
     * @return array An array of functions
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('full_url', [$this, 'getFullUrl']),
            new TwigFunction('app_path', [$this, 'getAppPath']),
        ];
    }

    /**
     * Generates an internal route path that stays correct on legacy pages.
     *
     * On legacy pages the RequestContext baseUrl is polluted with the legacy
     * front controller path (e.g. /en/private/calendar/calendar.php), which
     * breaks a plain path(). This forces the baseUrl back to the app mount
     * path so the generated URL keeps the /en/private prefix in every context.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    public function getAppPath($name, $parameters = [])
    {
        $baseUrl = $this->requestContext->getBaseUrl();
        $this->requestContext->setBaseUrl($this->mountPath);

        try {
            return $this->generator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
        } finally {
            $this->requestContext->setBaseUrl($baseUrl);
        }
    }

    /**
     * @param string $name
     * @param array  $parameters
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

    public function getHtmlLinkFromString($url, $blank = true)
    {
        if (false === $domain = parse_url((string) $url, \PHP_URL_HOST)) {
            return $url;
        }

        return \sprintf('<a href="%s"%s>%s</a>', $url, $blank ? ' target="_blank"' : '', $domain);
    }
}

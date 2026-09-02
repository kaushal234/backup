<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class LegacyLinksExtension extends AbstractExtension
{
    private readonly RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('dmsPortalLink', [$this, 'getDMSPortalHtmlLink']),
            new TwigFilter('dmsHtmlLink', [$this, 'getDMSHtmlLink']),
            new TwigFilter('downloadsHtmlLink', [$this, 'getDownloadsHtmlLink']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('dms', [$this, 'getDMSLink']),
            new TwigFunction('dms_portal', [$this, 'getDMSPortalLink']),
            new TwigFunction('downloads', [$this, 'getDownloadsLink']),
        ];
    }

    public function getDMSLink($dmsId)
    {
        if (null === $dmsId) {
            return;
        }

        return $this->router->generate('legacy_mis', [
            'm' => ['help', 'dms'],
            'id' => $dmsId,
        ]);
    }

    public function getDMSHtmlLink($dmsId)
    {
        if (null === $dmsId) {
            return;
        }

        return \sprintf('<a href="%s" class="dms-link">%d</a>', $this->getDMSLink($dmsId), $dmsId);
    }

    /**
     * @param int|string $dmsId
     *
     * @todo find a way to use the generator for an external url (seems impossible)
     */
    public function getDMSPortalLink($dmsId): string
    {
        $params = http_build_query([
            'm' => ['view'],
            'id' => $dmsId,
        ]);

        return 'https://dms.tld-group.com/index.php?'.$params;
    }

    public function getDMSPortalHtmlLink($dmsId)
    {
        if (null === $dmsId) {
            return;
        }

        return \sprintf('<a href="%s" class="dms-link">%d</a>', $this->getDMSPortalLink($dmsId), $dmsId);
    }

    public function getDownloadsLink($linkId)
    {
        if (null === $linkId) {
            return;
        }

        return $this->router->generate('legacy_downloads', [
            'mode' => 'record_view',
            'form_type' => 'main_tpl',
            'id' => $linkId,
        ]);
    }

    public function getDownloadsHtmlLink($linkId)
    {
        if (null === $linkId) {
            return;
        }

        return \sprintf('<a href="%s" class="download-link">%d</a>', $this->getDownloadsLink($linkId), $linkId);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'dms';
    }
}

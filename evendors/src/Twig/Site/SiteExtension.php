<?php

declare(strict_types=1);

namespace App\Twig\Site;

use App\Sdk\ClientInterface;
use App\Sdk\Resource\Site;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Str;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SiteExtension extends AbstractExtension
{
    /**
     * @var AccessibleCollectionInterface<int, Site>|null
     */
    private ?AccessibleCollectionInterface $sites = null;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_site_name', $this->getSiteName(...)),
        ];
    }

    public function getSiteName(string $identifier): string
    {
        return $this->cache->get(Str\format('evendors_site_name_%s', $identifier), function () use ($identifier): string {
            $this->sites ??= $this->client->findAll(Site::class);
            foreach ($this->sites as $site) {
                if ($site->siteID === $identifier) {
                    return $site->siteDescription;
                }
            }

            return $identifier;
        });
    }
}

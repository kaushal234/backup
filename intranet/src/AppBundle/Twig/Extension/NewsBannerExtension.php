<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use ApiBundle\Client;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NewsBannerExtension extends AbstractExtension
{
    public function __construct(
        private readonly Client $client,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('news', [$this, 'getNewsBanner']),
        ];
    }

    public function getNewsBanner(): ?array
    {
        $session = $this->requestStack->getSession();
        if (!$session instanceof Session) {
            return null;
        }

        $news = $this->client->findBy('news', ['banner' => true]);
        if (0 === $news->count()) {
            return null;
        }

        $news = $news->first();

        return [
            'majorIncident' => $news['majorIncident'],
            'bannerText' => \sprintf('<a class="text-white" href="/en/private/news/%s/show">%s <i class="fa fa-arrow-right" aria-hidden="true"></i></a>', $news['id'], $news['bannerText']),
        ];
    }
}

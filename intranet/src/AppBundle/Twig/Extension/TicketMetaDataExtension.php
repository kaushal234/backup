<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TicketMetaDataExtension extends AbstractExtension
{
    private readonly RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ticket_metadata', [$this, 'getTicketData']),
        ];
    }

    public function getTicketData(?string $module = null)
    {
        if (null === ($request = $this->requestStack->getCurrentRequest())) {
            return [];
        }

        return [
            'url' => $request->getRequestUri(),
            'hostname' => gethostname(),
            'request' => json_encode(array_filter($request->request->all(), static fn ($key) => false === mb_strpos((string) $key, 'pass'), \ARRAY_FILTER_USE_KEY)),
            'referer' => $request->headers->get('referer'),
            'module' => $module ?? $request->attributes->get('alvest_module'),
        ];
    }
}

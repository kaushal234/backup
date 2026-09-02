<?php

declare(strict_types=1);

namespace LegacyBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class ResponseLegacyIncludeListener implements EventSubscriberInterface
{
    private readonly Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$event->isMainRequest()) {
            return;
        }

        // Do not capture redirects or modify XML HTTP Requests
        if ($request->isXmlHttpRequest()) {
            return;
        }

        // Enable only for the legacy
        if (!$response->headers->has('X-Is-Legacy')) {
            return;
        }

        // Check response contains html
        if (!$response->headers->has('Content-Type')
            || false === mb_strpos($response->headers->get('Content-Type'), 'html')) {
            return;
        }

        $content = preg_replace_callback('|<tld:include\s+src="([^"]*)"\s*/>|', fn ($matches) => $this->doInclude($matches), $response->getContent());
        $response->setContent($content);
    }

    public function doInclude($matches)
    {
        return $this->twig->render($matches[1], [
            '_legacy' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}

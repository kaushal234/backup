<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Controller\EquipmentRecord\ShowPublicController;
use App\Http\Responder;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

/**
 * First application-level defensive layer for the public equipment search.
 *
 * Throttles requests per client IP (sliding window) on the equipment:show:public route only.
 * Beyond the threshold, it returns a 429 response with a Retry-After header and logs the
 * blocked request.
 */
final class PublicSearchRateLimiterSubscriber
{
    public function __construct(
        private readonly RateLimiterFactoryInterface $publicEquipmentSearchLimiter,
        private readonly Responder $responder,
        private readonly LoggerInterface $rateLimiterLogger,
    ) {
    }

    // Priority < 32 (RouterListener) so the "_route" attribute is already resolved.
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 10)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (ShowPublicController::PUBLIC_SEARCH_EQUIPMENT !== $request->attributes->get('_route')) {
            return;
        }

        $clientIp = $request->getClientIp() ?? '';
        $rateLimit = $this->publicEquipmentSearchLimiter->create($clientIp)->consume(1);

        if ($rateLimit->isAccepted()) {
            return;
        }

        $retryAfter = max(0, $rateLimit->getRetryAfter()->getTimestamp() - time());

        $this->rateLimiterLogger->warning('Public equipment search rate limit exceeded', [
            'ip' => $clientIp,
            'route' => ShowPublicController::PUBLIC_SEARCH_EQUIPMENT,
            'path' => $request->getPathInfo(),
            'limit' => $rateLimit->getLimit(),
            'retry_after' => $retryAfter,
        ]);

        $response = $this->responder->render(
            'errors/rate_limited.html.twig',
            ['retryAfter' => $retryAfter],
            Response::HTTP_TOO_MANY_REQUESTS,
            [
                'Retry-After' => (string) $retryAfter,
                'X-RateLimit-Limit' => (string) $rateLimit->getLimit(),
                'X-RateLimit-Remaining' => (string) $rateLimit->getRemainingTokens(),
            ],
        );

        $event->setResponse($response);
    }
}

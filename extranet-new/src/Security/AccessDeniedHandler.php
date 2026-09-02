<?php

declare(strict_types=1);

namespace App\Security;

use App\Http\Responder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

final readonly class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(private Responder $responder)
    {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): Response
    {
        $this->responder->flash('danger', 'security.warning.access_denied');

        $referer = $this->getSafeReferer($request);

        if (null !== $referer) {
            return $this->responder->redirect($referer);
        }

        return $this->responder->route('index');
    }

    /**
     * Returns the request referer only when it points to the current host and
     * is not the denied request itself, to avoid open redirects and redirect loops.
     */
    private function getSafeReferer(Request $request): ?string
    {
        $referer = $request->headers->get('referer');

        if (null === $referer || '' === $referer) {
            return null;
        }

        $refererHost = parse_url($referer, \PHP_URL_HOST);

        if (null !== $refererHost && $refererHost !== $request->getHost()) {
            return null;
        }

        if ($referer === $request->getUri()) {
            return null;
        }

        return $referer;
    }
}

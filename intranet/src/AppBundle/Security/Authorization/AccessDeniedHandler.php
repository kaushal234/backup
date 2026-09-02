<?php

declare(strict_types=1);

namespace AppBundle\Security\Authorization;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var RouterInterface */
    protected $router;

    public function __construct(TranslatorInterface $translator, RouterInterface $router)
    {
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * Convert the AccessDeniedException to a redirection to the login page, so the user can try to refresh his token.
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): RedirectResponse
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('error', $this->translator->trans('security.warning.access_denied'));

        $target = $request->headers->get('referer');
        if (null === $target) {
            $target = $this->router->generate('account_home');
        }

        return new RedirectResponse($target);
    }
}

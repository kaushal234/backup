<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CookieController extends AbstractController
{
    final public const COOKIES = ['ALVEST_STAGING'];

    #[Route(path: '/cookies/{cookie}', name: 'cookie_switcher', methods: 'GET')]
    public function cookieSwitch(Request $request, $cookie)
    {
        if (!$this->isGranted('FEATURE_COOKIE_'.$cookie)) {
            throw new AccessDeniedException('Access Denied.');
        }

        $domain = implode('.', \array_slice(explode('.', $request->getHttpHost()), -2, 2));

        $response = $request->headers->has('referer') ? $this->redirect($request->headers->get('referer')) : $this->redirectToRoute('home');

        if ($request->cookies->has($cookie)) {
            $response->headers->clearCookie($cookie, '/', $domain);

            return $response;
        }

        $response->headers->setCookie(Cookie::create(
            $cookie,
            $cookie,
            new \DateTimeImmutable('+1 day'),
            '/',
            $domain,
            false,
            true,
            true
        ));

        return $response;
    }
}

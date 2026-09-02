<?php

declare(strict_types=1);

namespace App\Controller;

use App\Auth\ApiAuthenticator;

class LoginController extends AbstractController
{
    public function bounce()
    {
        if (empty($token = $this->getRequest()->query()->get('token'))) {
            $this->getSession()->getFlashBag()->add('alert', _('Can not login, token empty or invalid'));

            return $this->redirectToHome();
        }

        $jwt = \tldUser::isTokenValid(\TldDatabase::escape($token));

        // set jwt to cookies
        if (null === $jwt) {
            $this->getSession()->getFlashBag()->add('alert', _('Can not login, token invalid'));

            return $this->redirectToHome();
        }

        try {
            $apiAuthenticator = new ApiAuthenticator($this->getClient());
            $apiAuthenticator->setCookie($jwt);
        } catch (\Exception $exception) {
            $this->getSession()->getFlashBag()->add('conf', _('Can not login, token invalid'));

            return $this->redirectToHome();
        }

        return $this->redirectToHome();
    }

    public function login()
    {
        if (empty($this->getRequest()->request()->get('login')) || empty($this->getRequest()->request()->get('pass'))) {
            $this->getSession()->getFlashBag()->add('alert', _('Data sent empty or invalid...'));

            return $this->redirectToHome();
        }

        try {
            $apiAuthenticator = new ApiAuthenticator($this->getClient());
            $apiAuthenticator->authenticate($this->getRequest()->request()->get('login'), mb_convert_encoding(stripslashes($this->getRequest()->request()->get('pass')), 'UTF-8'));
            $this->getSession()->getFlashBag()->add('conf', _('Logged in successfully...'));

            return $this->redirectToReferer();
        } catch (\Exception $exception) {
            $this->getSession()->getFlashBag()->add('conf', _('User or password incorrect'));

            error_log(\sprintf('DMS Auth to API error: [%s] %s', $exception->getCode(), $exception->getMessage()));

            return $this->redirectToHome();
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

class LogoutController extends AbstractController
{
    public function logout()
    {
        if (is_a($GLOBALS['user'], 'tldUser') && !empty($GLOBALS['user'])) {
            $GLOBALS['user']->logout();
        }
        $GLOBALS['user'] = null;

        if (isset($_REQUEST['redirect'])) {
            echo $_REQUEST['redirect'];
            header('Location: http://www.tld-gse.com/en/private/');
            exit;
        }

        $this->getSession()->getFlashBag()->add('warning', _('Logged out successfully...'));

        setcookie('_jwt', '', time() - 3600, '/', '', false, true);
        unset($GLOBALS['sess']['dms']['user'], $_COOKIE['_jwt']);
        $this->getSession()->remove('dms');

        return $this->redirectToHome();
    }
}

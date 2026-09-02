<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

(static function (): never {
    require_once dirname(__DIR__) . '/config/bootstrap.php';

    if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? '') {
        Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
    }

    if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? '') {
        Request::setTrustedHosts([$trustedHosts]);
    }

    if ($_SERVER['APP_DEBUG']) {
        umask(0000);

        Debug::enable();
    }

    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

    exit(0);
})();

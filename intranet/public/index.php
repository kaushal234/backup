<?php

use App\Kernel;
use Cake\Chronos\Chronos;
use LegacyBundle\Http\LegacyHandler;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();

    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = 443;
    }
}

if ('test' === $_SERVER['APP_ENV']) {
    Chronos::setTestNow('2020-01-01 00:00:00');
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_FOR|Request::HEADER_X_FORWARDED_HOST|Request::HEADER_X_FORWARDED_PROTO|Request::HEADER_X_FORWARDED_PORT|Request::HEADER_X_FORWARDED_PREFIX);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$sfRequest = Request::createFromGlobals();

try {
    // Try to handle the request from within Symfony
    $response = $kernel->handle($sfRequest, HttpKernelInterface::MAIN_REQUEST, false);
} catch (NotFoundHttpException $e) {
    // handle legacy
    $legacyHandler = $kernel->getContainer()->get(LegacyHandler::class);

    if (!$response = $legacyHandler->parse($sfRequest)) {
        $legacyHandler->bootLegacy();
        $logger = $kernel->getContainer()->get('monolog.logger.legacy');
        $request = $kernel->getContainer()->get('request_stack');
        $session = $request->getSession();
        if ($session->isStarted()) {
            $session->save();
        }
        try {
            require_once $legacyHandler->getLegacyPath();
            $response = $legacyHandler->handleResponse();
        } catch (\Exception $e) {
            // In case we have an error in the legacy, we want to be able to
            // have a nice error page instead of a blank page.
            $response = $legacyHandler->handleThrowable($e, $sfRequest);
        }
    }
}

$response->send();
$kernel->terminate($sfRequest, $response);

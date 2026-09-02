<?php

declare(strict_types=1);

namespace App;

use App\Client\ApiClient;
use App\Config\Globals;
use App\Config\Routing;
use App\HttpFoundation\MainRequest;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class App
{
    public $session;
    public $dotEnv;
    public $client;
    public $request;
    public $routes;
    public $language;

    public function __construct()
    {
        new Globals();

        session_start();
        if (!isset($_SESSION['sess'])) {
            $_SESSION['sess'] = null;
        }

        $GLOBALS['sess'] = &$_SESSION['sess'];

        $this->session = new Session(new PhpBridgeSessionStorage());
        $this->session->start();

        $this->dotEnv = new Dotenv();
        $this->dotEnv->loadEnv(__DIR__.'/../.env');

        $this->client = new ApiClient($_ENV['API_URL']);

        $request = Request::createFromGlobals();
        $this->request = new MainRequest($request);

        $this->routes = new RouteCollection();
        $routing = new Routing($this->client);
        $routing->map($this->routes);

        $this->language = new Language($request);
    }

    public function run(): void
    {
        // Set user in session
        if (!$this->client->isAuthenticated() || (null === $this->client->getUserId())) {
            $GLOBALS['user'] = null;
            $GLOBALS['sess']['dms']['user'] = null;
            $this->session->remove('dms');
        } else {
            if (
                !$this->session->has('dms')
                || !isset($this->session->has('dms')['user'])
                || null === $this->session->get('dms')['user']
                || !isset($GLOBALS['sess']['dms']['user'])
                || empty($GLOBALS['sess']['dms']['user'])
            ) {
                try {
                    $user = json_decode($this->client->request('GET', '/me')->getContent(), true, \JSON_THROW_ON_ERROR);

                    $GLOBALS['user'] = new \tldUser($user['legacyId']);

                    $GLOBALS['sess']['dms']['user'] = serialize($GLOBALS['user']);
                    $this->session->set('dms', ['user' => serialize($GLOBALS['user'])]);
                } catch (\Exception $exception) {
                    $this->request->getRequest()->cookies->remove('_jwt');
                    unset($_COOKIE['_jwt']);
                    $GLOBALS['_ERROR'][] = _('Something went wrong while fetching your information, please retry');
                }
            }
        }

        // Routing
        try {
            $matcher = new UrlMatcher($this->routes, new RequestContext('', $this->request->getRequest()->getMethod()));
            $parameters = $matcher->match($this->request->getCurrentRoute());

            // todo : make better implementation of dynamic call
            $controller = new $parameters['_controller']($this->client, $this->session, $this->routes, $this->request);
            $response = $controller->{$parameters['_route']}();

            if ($response instanceof Response) {
                $response->send();
                exit;
            }
        } catch (ResourceNotFoundException $e) {
            // use the standard switch case control
            if (!$this->client->isAuthenticated() || (null === $this->client->getUserId())) {
                $GLOBALS['_ERROR'][] = _('Authentication is required');
            }
        } catch (MethodNotAllowedException $e) {
            $GLOBALS['_ERROR'][] = _('Page not found');
        }

        // User language
        $userLanguage = $this->language->getLanguage();
        $GLOBALS['_LANG'] = $userLanguage;
        $GLOBALS['sess']['lang'] = $userLanguage;
        $this->session->set('lang', $userLanguage);

        // Charset
        $charset = $GLOBALS['_CHARSET'] = Language::AVAILABLE_LANGUAGES[$userLanguage]['charset'];
        header("Content-Type:text/html; charset=$charset");

        // translation domain
        bindtextdomain('dms', $GLOBALS['DMS_PATH'].'/locale');
        textdomain('dms');
        bind_textdomain_codeset('dms', Language::AVAILABLE_LANGUAGES[$userLanguage]['charset']);
    }

    public function isAuthorized(): bool
    {
        if (!$this->client->isAuthenticated()) {
            return false;
        }

        if (!\array_key_exists('dms', $GLOBALS['sess'])) {
            $GLOBALS['_ERROR'][] = _('Authentication failed, please log in again');

            return false;
        }

        $user = $GLOBALS['user'] = unserialize($GLOBALS['sess']['dms']['user']);

        if (!$user->isInGroup(['acl_auth_INTRANET'])) {
            $this->session->getFlashBag()->add('alert', _('You are not allowed to access DMS portal !'));
            $user = null;
            $GLOBALS['sess']['dms']['user'] = null;
            $this->session->set('dms', ['user' => null]);

            return false;
        }

        return true;
    }
}

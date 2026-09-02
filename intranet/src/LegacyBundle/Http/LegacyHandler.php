<?php

declare(strict_types=1);

namespace LegacyBundle\Http;

use Exception;
use LegacyBundle\HttpKernel\LegacyHttpKernel as HttpKernel;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * A class which handles the legacy.
 */
class LegacyHandler
{
    private readonly HttpKernel $httpKernel;
    /** @var string */
    private $legacyDir;
    /** @var string */
    private $basePath;
    private ?Stopwatch $stopwatch = null;
    private readonly TokenStorageInterface $tokenStorage;
    private ?string $legacyPath = null;
    private ?Request $request = null;
    /** @var bool */
    private $debug;
    private readonly array $folderWhitelist;
    private ?int $previousErrorLevel = null;
    private ?int $previousErrorHandlerScopeAt = null;

    public function __construct(HttpKernel $httpKernel, $legacyDir, TokenStorageInterface $tokenStorage, ?Stopwatch $stopwatch = null, $debug = false)
    {
        $this->httpKernel = $httpKernel;
        $this->legacyDir = $legacyDir;
        $this->basePath = getcwd();
        $this->tokenStorage = $tokenStorage;
        $this->stopwatch = $stopwatch;
        $this->debug = $debug;
        $this->folderWhitelist = [
            $legacyDir,
            $legacyDir.'/uploads',
        ];
    }

    public function getLegacyPath()
    {
        return $this->legacyPath;
    }

    /**
     * Parses the request.
     *
     * Handles the request and returns a response if we are handling
     * a static file. Also checks whether we have access to the specified
     * file.
     *
     * @return Response|null
     */
    public function parse(Request $request)
    {
        $this->stopwatchStop('Symfony\Component\HttpKernel\EventListener\RouterListener');
        $this->stopwatchStart('legacy');
        $this->request = $request;
        $path = explode('?', $this->request->getPathInfo())[0];
        $path = urldecode(ltrim(rtrim($path, '/'), '/'));
        $path = $this->legacyDir.'/'.$path;

        if (!realpath($path)) {
            return $this->handleNotFound($request);
        }

        $authorized = false;
        foreach ($this->folderWhitelist as $folder) {
            if (!realpath($folder)) {
                throw new \Exception("Misconfiguration: $folder does not exist on host");
            }

            if (0 !== mb_strpos(realpath($path), realpath($folder))) {
                continue;
            }

            $authorized = true;
        }

        if (!$authorized) {
            throw new AccessDeniedException(\sprintf('You are forbidden to access "%s"', $path));
        }

        if (is_dir($path)) {
            if ($response = $this->handleTrailingSlash($request)) {
                return $response;
            }

            foreach (['/index.php', '/index.htm'] as $extension) {
                if (file_exists($path.$extension)) {
                    $path .= $extension;
                    break;
                }
            }

            if (!file_exists($path)) {
                return $this->handleNotFound($request);
            }
        }

        if ('php' !== mb_strtolower(pathinfo($path, \PATHINFO_EXTENSION))) {
            return new Response('', Response::HTTP_OK, [
                'X-Sendfile' => $path,
                'Content-Type' => (new \finfo(\FILEINFO_MIME_TYPE))->file($path),
            ]);
        }

        $this->legacyPath = $path;

        return null;
    }

    /**
     * Boots the legacy.
     *
     * This method overrides the error level and a few superglobals to make the
     * legacy work correctly. Also activates output buffering, in order to fetch
     * the legacy's output.
     */
    public function bootLegacy()
    {
        // Override error reporting to prevent a few exceptions when handling legacy code
        $this->switchErrorLevel(
            \E_ALL & ~\E_DEPRECATED & ~\E_STRICT & ~\E_NOTICE & ~\E_WARNING
        );

        // Avoid local variables to be preserved in errors scopes.
        // see https://github.com/symfony/symfony/pull/20519#issuecomment-265174679
        $this->setErrorHandlerScopeAt(\E_ERROR);

        if (null !== $token = $this->tokenStorage->getToken()) {
            $this->request->server->set('PHP_AUTH_USER', $token->getUserIdentifier());
            $this->request->server->set('PHP_AUTH_PW', '');
        }

        // Override some globals, to fake direct entry from the legacy
        $url = $this->request->server->get('REDIRECT_SCRIPT_URL');
        $this->request->server->set('SCRIPT_NAME', $url);
        $this->request->server->set('PHP_SELF', $url);
        $this->request->server->set('SCRIPT_FILENAME', $this->legacyPath);
        $this->request->overrideGlobals();

        // Change path to the script's path
        chdir(\dirname($this->legacyPath));

        // Enable output buffering to be able to fetch the legacy's content
        // and encapsulate later in a response object.
        ob_start();
    }

    /**
     * Handles the response.
     *
     * Restores the default error level, and encapsulates the legacy response's
     * content in a Symfony response.
     *
     * @return Response
     */
    public function handleResponse()
    {
        // Restore error handler scoping
        $this->setErrorHandlerScopeAt($this->previousErrorHandlerScopeAt);

        // Restore error reporting in Symfony part of the request.
        $this->switchErrorLevel($this->previousErrorLevel);

        // Fetch content from the legacy (via output buffering).
        $content = ob_get_clean();

        // Restore the path
        chdir($this->basePath);
        $rawHeaders = array_map(static fn ($header) => explode(': ', (string) $header), headers_list());

        $headers = [
            'X-Is-Legacy' => '1',
        ];
        foreach ($rawHeaders as $header) {
            [$k, $v] = $header;
            header_remove($k);
            $headers[$k] = $v;
        }

        // Check content-type to inject the correct content-type header in the response.
        if (preg_match(
            '#Content-Type["\']\s+content=[\'"](text/html; charset=[a-zA-Z\-0-9]+[^\-])[\'"]#',
            $content,
            $matches
        )) {
            $headers['Content-Type'] = $matches[1];
        }

        // Encapsulate legacy response in Symfony
        $response = new Response($content, http_response_code(), $headers);

        $this->stopwatchStop('legacy');

        // Add additional stuff from Symfony (additional headers, WDT when in dev, etc.)
        return $this->httpKernel->filterResponse($response, $this->request);
    }

    /**
     * Handles an exception, and wraps it in a Symfony response.
     *
     * @return Response
     */
    public function handleThrowable(\Exception $e, Request $request)
    {
        $this->switchErrorLevel($this->previousErrorLevel);

        return $this->httpKernel->handleThrowable($e, $request);
    }

    public function handleTrailingSlash(Request $request)
    {
        $parts = parse_url($request->getUri());

        if (isset($parts['path']) && '/' === mb_substr($parts['path'], -1)) {
            return;
        }

        $parts['path'] .= '/';

        $url = $parts['scheme'].'://'.$parts['host'].$parts['path'];

        if (isset($parts['query'])) {
            $url .= '?'.$parts['query'];
        }

        return new RedirectResponse($url);
    }

    /**
     * Set the error handler level at which the handler should provide a context.
     */
    private function setErrorHandlerScopeAt($scopeAt)
    {
        $errorHandler = set_error_handler(null);
        restore_error_handler();
        if (!\is_array($errorHandler) || !\is_object($errorHandler[0]) || !$errorHandler[0] instanceof ErrorHandler) {
            return;
        }

        $this->previousErrorHandlerScopeAt = $errorHandler[0]->scopeAt($scopeAt, true);
    }

    /**
     * Switches the error level.
     *
     * @param int $errorLevel
     */
    private function switchErrorLevel($errorLevel)
    {
        $this->previousErrorLevel = error_reporting($errorLevel);
    }

    /**
     * Starts a stopwatch section, when debug mode is activated.
     *
     * @param string $name
     */
    private function stopwatchStart($name)
    {
        if ($this->debug) {
            $this->stopwatch->start($name);
        }
    }

    /**
     * Stops a stopwatch section, when debug mode is activated.
     *
     * @param string $name
     */
    private function stopwatchStop($name)
    {
        if ($this->debug) {
            $this->stopwatch->stop($name);
        }
    }

    /**
     * A simple helper to throw a NotFoundHttpException.
     *
     * @return Response
     */
    private function handleNotFound(Request $request)
    {
        return $this->handleThrowable(new NotFoundHttpException(), $request);
    }
}

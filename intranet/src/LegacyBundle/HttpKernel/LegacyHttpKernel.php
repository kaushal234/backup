<?php

declare(strict_types=1);

namespace LegacyBundle\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 * The legacy kernel.
 *
 * This class encapsulates the default HttpKernel, in order to make some
 * private methods accessible, which are needed for bootstrapping the legacy
 * code.
 */
class LegacyHttpKernel implements HttpKernelInterface, TerminableInterface
{
    private readonly HttpKernel $kernel;
    private readonly RequestStack $requestStack;

    public function __construct(HttpKernel $kernel, RequestStack $requestStack)
    {
        $this->kernel = $kernel;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MAIN_REQUEST, $catch = true): Response
    {
        $request->headers->set('X-Php-Ob-Level', (string) ob_get_level());

        try {
            return $this->handleRaw($request);
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->handleThrowable($e, $request);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(Request $request, Response $response): void
    {
        $this->kernel->terminate($request, $response);
    }

    public function filterResponse(Response $response, Request $request)
    {
        $response = $this->callEmbeddedHttpKernelMethod('filterResponse', $response, $request);
        $this->requestStack->pop();

        return $response;
    }

    public function handleThrowable(\Exception $e, Request $request, $type = self::MAIN_REQUEST)
    {
        return $this->callEmbeddedHttpKernelMethod('handleThrowable', $e, $request, $type);
    }

    private function handleRaw(Request $request, $type = self::MAIN_REQUEST)
    {
        $this->requestStack->push($request);

        return $this->callEmbeddedHttpKernelMethod('handleRaw', $request, $type);
    }

    /**
     * Calls a private method of the embedded HttpKernel.
     */
    private function callEmbeddedHttpKernelMethod(...$args)
    {
        $method = array_shift($args);
        array_unshift($args, $this->kernel);
        $args[] = HttpKernelInterface::MAIN_REQUEST;
        $reflObject = new \ReflectionObject($this->kernel);
        $reflMethod = $reflObject->getMethod($method);
        $reflMethod->setAccessible(true);

        return \call_user_func_array($reflMethod->invoke(...), $args);
    }
}

<?php

declare(strict_types=1);

namespace App\EventListener;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ApiExceptionListener
{
    protected RequestStack $requestStack;

    protected RouterInterface $router;

    protected LoggerInterface $logger;

    protected string $env;

    public function __construct(string $env, RequestStack $requestStack, RouterInterface $router, LoggerInterface $logger)
    {
        $this->env = $env;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ClientExceptionInterface && Response::HTTP_NOT_FOUND === $exception->getCode()) {
            throw new NotFoundHttpException();
        }

        if ((
            $exception instanceof HandlerFailedException
            || $exception instanceof TransportExceptionInterface
            || $exception instanceof ClientExceptionInterface
        ) && 'dev' !== $this->env) {
            $event->setResponse($this->provideError($exception));
        }
    }

    protected function provideError(Exception $exception): Response
    {
        if (Response::HTTP_NOT_FOUND === $exception->getCode()) {
            throw new NotFoundHttpException();
        }

        $session = $this->requestStack->getSession();
        if (!$session instanceof Session) {
            throw new SessionNotFoundException();
        }

        $session->getFlashBag()->add('danger', 'security.not_responding');

        $this->logger->error($exception->getMessage());

        return $this->redirectResponse();
    }

    /**
     * Redirect to index or redirect to logout if we have an API error on index.
     */
    protected function redirectResponse(): Response
    {
        $currentRoute = $this->requestStack->getMainRequest()->attributes->get('_route');

        if ('index' !== $currentRoute) {
            return new RedirectResponse($this->router->generate('index'));
        }

        return new RedirectResponse($this->router->generate('security:logout'));
    }
}

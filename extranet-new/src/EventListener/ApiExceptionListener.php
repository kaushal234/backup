<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpClient\Exception\ClientException;
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
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(event: 'kernel.exception')]
class ApiExceptionListener
{
    protected RequestStack $requestStack;

    protected RouterInterface $router;

    protected LoggerInterface $logger;

    private TranslatorInterface $translator;

    public function __construct(RequestStack $requestStack, RouterInterface $router, LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ClientExceptionInterface && Response::HTTP_NOT_FOUND === $exception->getCode()) {
            throw new NotFoundHttpException();
        }

        if (
            $exception instanceof HandlerFailedException
            || $exception instanceof TransportExceptionInterface
            || $exception instanceof ClientExceptionInterface
        ) {
            $event->setResponse($this->provideError($exception));
        }
    }

    protected function provideError(\Exception $exception): Response
    {
        if (Response::HTTP_NOT_FOUND === $exception->getCode()) {
            throw new NotFoundHttpException();
        }

        $session = $this->requestStack->getSession();
        if (!$session instanceof Session) {
            throw new SessionNotFoundException();
        }

        if ($exception instanceof ClientException && Response::HTTP_UNPROCESSABLE_ENTITY === $exception->getCode()) {
            $errors = preg_split('/\r\n|\r|\n/', $exception->getResponse()->toArray(false)['hydra:description']);

            $fieldsList = [];
            foreach ($errors as $error) {
                $keyValueError = explode(':', $error);
                $fieldsList[] = $keyValueError[0];
            }

            $session->getFlashBag()->add('danger', $this->translator->trans('violations', ['%fields%' => implode(', ', array_unique($fieldsList))], 'validators'));
        } else {
            $session->getFlashBag()->add('danger', 'security.not_responding');
        }

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

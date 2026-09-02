<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use ApiBundle\Client;
use AppBundle\Form\Type\IdSearchType;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(event: RequestEvent::class, method: 'onKernelRequest')]
readonly class QuickSearchControllerListener
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private Client $client,
        private RouterInterface $router,
        private TranslatorInterface $translator,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->getRequest()->isMethod(Request::METHOD_POST) || [] === $event->getRequest()->request->all(IdSearchType::NAME)) {
            return;
        }

        $idSearchForm = $this->formFactory->create(IdSearchType::class)->handleRequest($event->getRequest());

        if (
            $idSearchForm->isSubmitted()
            && $idSearchForm->isValid()
            && ($redirectRoute = $idSearchForm->get('redirect_route')->getData())
            && ($apiRoute = $idSearchForm->get('api_route')->getData())
        ) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $this->client->get(\sprintf('%s/%s', $apiRoute, $id));

                $event->stopPropagation();
                $event->setResponse(new RedirectResponse($this->router->generate($redirectRoute, ['id' => $id]), Response::HTTP_FOUND));
            } catch (ClientExceptionInterface) {
                /** @var Session $session */
                $session = $event->getRequest()->getSession();
                $session->getFlashBag()->add('error', $this->translator->trans('errors.not_exist', ['%id%' => $id]));
            }
        }
    }
}

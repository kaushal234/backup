<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use ApiBundle\Security\Core\Authentication\JwtCookieFactory;
use AppBundle\Twig\Extension\ExternalUrlExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class ImpersonateUserListener implements EventSubscriberInterface
{
    final public const SWITCH_PARAMETER = '_impersonate';

    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
        private readonly ExternalUrlExtension $urlExtension,
        private readonly JwtCookieFactory $cookieFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['handleUserSwitch', 7],
            ],
        ];
    }

    /**
     * @return RedirectResponse|void
     */
    public function handleUserSwitch(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->query->has(self::SWITCH_PARAMETER)) {
            return;
        }

        $target = $request->query->get(self::SWITCH_PARAMETER);
        if (!\in_array(Iri::type($target), ['/sales/extranet_users', '/purchasing/vendor_users'], true)) {
            throw new BadRequestHttpException(\sprintf('Can not switch to "%s"', Iri::type($target)));
        }
        try {
            $switchResponse = $this->client->post('/user-tokens/'.Iri::id($target));
        } catch (\Exception $e) {
            /** @var Session $session */
            $session = $request->getSession();
            $session->getFlashBag()
                ->add(
                    'error',
                    $this->translator->trans('contacts.messages.error.permission', [], 'contacts')
                );

            $request->query->remove(self::SWITCH_PARAMETER);
            $request->server->set('QUERY_STRING', http_build_query($request->query->all()));

            return new RedirectResponse($request->getUri());
        }

        switch (Iri::type($target)) {
            case '/sales/extranet_users':
                $userIdentifier = $request->query->get('_userIdentifier');
                $response = new RedirectResponse($this->urlExtension->getFullUrl('portal_extranet_impersonate').'?_userIdentifier='.$userIdentifier);
                $event->setResponse($response);
                $this->cookieFactory->setDomainFromRequestHttpHost($request->getHttpHost());
                break;
            case '/purchasing/vendor_users':
                $userIdentifier = $request->query->get('_userIdentifier');
                $response = new RedirectResponse($this->urlExtension->getFullUrl('portal_evendor_impersonate').'?_userIdentifier='.$userIdentifier);
                $event->setResponse($response);
                $this->cookieFactory->setDomainFromRequestHttpHost($request->getHttpHost());
                break;
        }

        $response->headers->setCookie($this->cookieFactory->generate($switchResponse['token']));
        $event->setResponse($response);
    }
}

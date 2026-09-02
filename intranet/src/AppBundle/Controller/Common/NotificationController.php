<?php

declare(strict_types=1);

namespace AppBundle\Controller\Common;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Notifications\NotificationsDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), Client::class, Security::class, TranslatorInterface::class];
    }

    #[Route(path: '/notifications', name: 'notifications_index', methods: 'GET')]
    #[Template('notifications/home.html.twig')]
    public function home(#[CurrentUser] User $user, Request $request)
    {
        $datatable = $this->createDataTable(NotificationsDataTableType::class, NotificationsDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'notificationDatatable' => $datatable->createView(),
        ];
    }

    #[Route('/notification/{id}/read-and-redirect', name: 'read_and_redirect_notification', methods: ['GET'])]
    public function readAndRedirect(
        #[ApiValueResolverAttribute] ApiData $notification,
    ) {
        try {
            $this->container->get(Client::class)->save('notifications', [
                '@id' => $notification->getIri(),
                'unread' => false,
            ]);
        } catch (ClientException) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('notifications.error.redirect', [], 'notifications')
            );
        }

        $targetUrl = $notification['url'] ?? '#';

        return $this->redirect($targetUrl);
    }

    #[Route(path: '/notification/{id}', name: 'read_notifications', methods: 'GET')]
    public function readNotification(#[ApiValueResolverAttribute] ApiData $notification): JsonResponse
    {
        try {
            $this->container->get(Client::class)->save('notifications', ['@id' => $notification->getIri(), 'unread' => false]);
        } catch (ClientException) {
        }

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/my_notifications', name: 'notifications_ajax', options: ['expose' => true], methods: 'GET')]
    public function notificationsAjax(#[CurrentUser] User $user)
    {
        try {
            $notifications = $this->container->get(Client::class)->findBy('notifications', [], ['createdAt' => 'DESC'], ['raw_results' => true]);
        } catch (ClientException) {
            return new JsonResponse([]);
        }

        return new JsonResponse($notifications['hydra:member']);
    }

    #[Route(path: '/remove-notification/{id}', name: 'remove_notifications_ajax', options: ['expose' => true], methods: 'GET')]
    public function removeNotificationAjax($id)
    {
        try {
            $this->container->get(Client::class)->remove('notifications', $id);
        } catch (ClientException) {
            // do nothing
        }

        return new JsonResponse();
    }

    #[Route(path: '/read-notification/{id}', name: 'read_notifications_ajax', options: ['expose' => true], methods: 'GET')]
    public function readNotificationAjax(#[ApiValueResolverAttribute] ApiData $notification)
    {
        try {
            $this->container->get(Client::class)->save('notifications', ['@id' => $notification->getIri(), 'unread' => false]);
        } catch (ClientException) {
            // do nothing
        }

        return new JsonResponse();
    }

    #[Route(path: '/read-all-notification', name: 'read_all_notifications_ajax', options: ['expose' => true], methods: 'GET')]
    public function readAllNotificationAjax()
    {
        try {
            $this->container->get(Client::class)->request('notifications/read_all', null, null, Request::METHOD_POST);
        } catch (ClientException) {
            // do nothing
        }

        return new JsonResponse();
    }

    #[Route(path: '/read-all', name: 'read_all_notifications', methods: 'GET')]
    public function readAllNotification()
    {
        try {
            $this->container->get(Client::class)->request('notifications/read_all', null, null, Request::METHOD_POST);
        } catch (ClientException) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('notifications.error.delete_all', [], 'notifications')
            );
        }

        return $this->redirectToRoute('notifications_index');
    }

    #[Route(path: '/delete-all-notification', name: 'delete_all_notifications_ajax', options: ['expose' => true], methods: 'GET')]
    public function deleteAllNotificationAjax()
    {
        try {
            $this->container->get(Client::class)->request('notifications/delete_all', null, null, Request::METHOD_POST);
        } catch (ClientException) {
            // do nothing
        }

        return new JsonResponse();
    }

    #[Route(path: '/delete-all', name: 'delete_all_notifications', methods: 'GET')]
    public function deleteAllNotification()
    {
        try {
            $this->container->get(Client::class)->request('notifications/delete_all', null, null, Request::METHOD_POST);
        } catch (ClientException) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('notifications.error.delete_all', [], 'notifications')
            );
        }

        return $this->redirectToRoute('notifications_index');
    }
}

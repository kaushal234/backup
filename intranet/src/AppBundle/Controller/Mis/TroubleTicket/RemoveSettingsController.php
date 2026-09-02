<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use AppBundle\Manager\SettingsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class RemoveSettingsController extends AbstractController
{
    public function __construct(
        private readonly SettingsManager $settingsManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/subscriptions/remove', name: 'trouble_ticket_remove_settings', methods: 'GET|POST')]
    public function __invoke(): RedirectResponse
    {
        try {
            $this->settingsManager->remove('tts.subscriptions');
            $this->addFlash('success', $this->translator->trans('trouble_ticket.success.subscription_remove', [], 'trouble_ticket'));
        } catch (ClientException $exception) {
            $this->addFlash('error', \sprintf('%s. Reason: %s', $this->translator->trans('trouble_ticket.errors.subscription', [], 'trouble_ticket'), $exception->getMessage()));
        }

        return $this->redirectToRoute('trouble_ticket_subscriptions');
    }
}

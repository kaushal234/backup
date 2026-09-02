<?php

declare(strict_types=1);

namespace AppBundle\Controller\Subscription;

use AppBundle\Manager\SettingsManager;
use AppBundle\Registry\SubscriptionRegistry;
use AppBundle\Subscription\SubscriptionFormFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/subscriptions')]
class SubscriptionController extends AbstractController
{
    public function __construct(
        private readonly SettingsManager $settingsManager,
        private readonly TranslatorInterface $translator,
        private readonly SubscriptionFormFactory $formFactory,
        private readonly SubscriptionRegistry $subscriptionRegistry,
    ) {
    }

    #[Route(path: '/{settingKey}', name: 'subscriptions', methods: 'POST')]
    public function subscriptions(Request $request, string $settingKey): RedirectResponse|array
    {
        $subscription = $this->subscriptionRegistry->get($settingKey);
        $form = $this->formFactory->create($settingKey);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SubmitButton $deleteButton */
            $deleteButton = $form->get('delete');
            if ($deleteButton->isClicked()) {
                try {
                    $this->settingsManager->remove($settingKey);
                    $this->addFlash('success', $this->translator->trans('subscriptions.success.remove'));
                } catch (ClientException $exception) {
                    $this->addFlash('error', $this->translator->trans('subscriptions.errors.remove'));
                }

                return $this->redirectToRoute($subscription->getRoute());
            }

            try {
                $this->settingsManager->set($settingKey, array_filter($form->getData()));
                $this->addFlash('success', $this->translator->trans('subscriptions.success.create'));
            } catch (ClientException $exception) {
                $this->addFlash('error', $this->translator->trans('subscriptions.errors.create'));
            }
        }

        return $this->redirectToRoute($subscription->getRoute());
    }
}

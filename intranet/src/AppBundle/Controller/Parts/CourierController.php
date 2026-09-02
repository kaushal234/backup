<?php

declare(strict_types=1);

namespace AppBundle\Controller\Parts;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Parts\Courier\CourierBatchType;
use AppBundle\Form\Type\Parts\Courier\CourierType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/parts/couriers', defaults: ['alvest_module' => 'SPR'])]
class CourierController extends AbstractController
{
    final public const RESOURCE_URL = 'parts/couriers';
    final public const TRANSLATION_DOMAIN = 'courier';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class]);
    }

    #[Route(path: '', name: 'courier_home', methods: ['GET|POST'])]
    #[Template('parts/courier/home.html.twig')]
    public function home(
        Request $request,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'filters' => ['order' => ['name' => 'ASC']]])] HydraCollection $couriers)
    {
        $indexedCouriers = $couriers->getIndexedCollection('id');
        $form = $this->createForm(CourierBatchType::class, ['couriers' => $indexedCouriers])->handleRequest($request);

        $translator = $this->container->get(TranslatorInterface::class);
        $success = false;
        if ($form->isSubmitted()) {
            foreach ($form->get('couriers')->getData() as $data) {
                $originalCourier = $indexedCouriers[$data['id']];

                if ($data === $originalCourier) {
                    continue;
                }

                try {
                    $this->container->get(Client::class)->save(self::RESOURCE_URL, $data);
                    $success = true;
                } catch (ClientException $e) {
                    $errors = json_decode($e->getResponse()->getContent(false), true);
                    foreach ($errors['violations'] as $error) {
                        $this->addFlash('warning', $error['message']);
                    }
                }
            }

            if ($success) {
                $this->addFlash('success', $translator->trans('courier.message.edit.success', [], self::TRANSLATION_DOMAIN));
            }
        }

        $formAdd = $this->createForm(CourierType::class, [], ['add' => true])->handleRequest($request);
        if ($formAdd->isSubmitted() && $formAdd->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $formAdd->getData());
            } catch (ClientException $e) {
                $errorDescription = json_decode($e->getResponse()->getContent(false), true);
                $this->addFlash(
                    'error',
                    \sprintf('%s. %s', $translator->trans('courier.message.add.error', [], self::TRANSLATION_DOMAIN), $errorDescription['hydra:description'])
                );
            }

            $this->addFlash('success', $translator->trans('courier.message.add.success', [], self::TRANSLATION_DOMAIN));

            return $this->redirectToRoute('courier_home');
        }

        return [
            'couriers' => $indexedCouriers,
            'form' => $form->createView(),
            'formAdd' => $formAdd->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'courier_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_COURIER_ADMIN')]
    public function delete(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $courier): RedirectResponse
    {
        $translator = $this->container->get(TranslatorInterface::class);
        if (!$this->isCsrfTokenValid('delete_courier', $request->query->get('_token'))) {
            $this->addFlash('error', $translator->trans('security.error.csrf', [], 'messages'));

            return $this->redirectToRoute('courier_home');
        }

        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $courier['id']);
            $this->addFlash('success', $translator->trans('courier.message.delete.success', [], self::TRANSLATION_DOMAIN));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $translator->trans('courier.message.delete.error', [], self::TRANSLATION_DOMAIN), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('courier_home');
    }
}

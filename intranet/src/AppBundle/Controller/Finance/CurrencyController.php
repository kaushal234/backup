<?php

declare(strict_types=1);

namespace AppBundle\Controller\Finance;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Form\Type\Finance\CurrencyType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/finance/currencies', defaults: ['alvest_module' => 'FRX'])]
class CurrencyController extends AbstractController
{
    final public const RESOURCE_URL = 'finance/currencies';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class, FormFactoryInterface::class]);
    }

    #[Route(path: '/add', name: 'currency_add', methods: ['POST|GET'])]
    #[Template('finance/currency/add.html.twig')]
    #[IsGranted('FEATURE_CURRENCY_WRITE')]
    public function addCurrencies(Request $request)
    {
        $form = $this->container->get(FormFactoryInterface::class)->createNamed(
            'currency_form',
            CurrencyType::class
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('currency.add.success', [], 'forex')
                );

                return $this->redirectToRoute('forex_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}

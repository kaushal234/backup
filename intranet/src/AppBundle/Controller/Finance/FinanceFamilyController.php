<?php

declare(strict_types=1);

namespace AppBundle\Controller\Finance;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Finance\FinanceFamilyType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/finance', defaults: ['alvest_module' => 'CAT', 'breadcrumb_label' => 'menu.finance_families.title', 'moduleDomain' => 'finance_family'])]
class FinanceFamilyController extends AbstractController
{
    private readonly Client $client;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
    }

    #[Route(path: '/finance-families', name: 'finance_family_home', methods: ['GET'])]
    #[Template('finance/finance-families/home.html.twig')]
    #[IsGranted('FEATURE_FINANCE_FAMILY_PRICING_READ')]
    public function home()
    {
        $connectedUser = $this->client->get('me');
        $ssos = $this->client->findBy('locations', ['capability.sso' => true], ['name' => 'asc'], ['raw_results' => true]);
        $factories = $this->client->findBy('locations', ['capability.factory' => true], ['name' => 'asc'], ['raw_results' => true]);
        $financeFamilies = $this->client->findBy('finance/finance_families',
            [],
            ['name' => 'ASC'],
            ['raw_results' => true]);

        $financeFamiliyPricings = $this->client->findBy('finance/finance_family_pricings',
            [],
            [],
            ['raw_results' => true]);

        $financeFamilies = array_reduce((array) ($financeFamilies['hydra:member'] ?? []), static function ($memo, $financeFamily) use ($financeFamiliyPricings, $ssos) {
            foreach ($financeFamiliyPricings['hydra:member'] as $financeFamilyPricing) {
                if ($financeFamilyPricing['financeFamily']['@id'] === $financeFamily['@id']) {
                    $financeFamily['pricings'][] = $financeFamilyPricing;
                }
            }

            if (\array_key_exists('pricings', $financeFamily)) {
                foreach ($financeFamily['pricings'] as $key => $pricing) {
                    $newKey = \sprintf('%s-%s', $pricing['sso'], $pricing['factory']);
                    $pricing['check'] = \in_array($pricing['factory'], $financeFamily['factories'], true);
                    $financeFamily['pricings'][$newKey] = $pricing;
                    unset($financeFamily['pricings'][$key]);
                }

                foreach ($financeFamily['factories'] as $factory) {
                    foreach ($ssos['hydra:member'] as $sso) {
                        $key = \sprintf('%s-%s', $sso['@id'], $factory);
                        if (!\array_key_exists($key, $financeFamily['pricings'])) {
                            $financeFamily['pricings'][$key] = ['check' => true];
                        }
                    }
                }
            }

            $memo[] = $financeFamily;

            return $memo;
        }, []);

        return [
            'initialState' => [
                'user' => [
                    'details' => $connectedUser,
                ],
                'finance' => [
                    'financeFamilies' => $financeFamilies,
                ],
                'location' => [
                    'ssos' => $ssos['hydra:member'],
                    'factories' => $factories['hydra:member'],
                ],
            ],
        ];
    }

    #[Route(path: '/finance-families/{id}/show', name: 'finance_family_show', methods: ['GET'])]
    #[Template('finance/finance-families/show.html.twig')]
    #[IsGranted('FEATURE_FINANCE_FAMILY_PRICING_READ')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => 'finance/finance_families'])] ApiData $financeFamily)
    {
        $products = $this->client->findBy('sales/products', ['financeFamily' => $financeFamily['@id']]);

        return ['financeFamily' => $financeFamily, 'products' => $products];
    }

    #[Route(path: '/finance-families/{id}/edit', name: 'finance_family_edit', methods: ['GET|POST'])]
    #[Template('finance/finance-families/edit.html.twig')]
    #[IsGranted('FEATURE_FINANCE_FAMILY_ADMIN')]
    public function edit(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'finance/finance_families'])] ApiData $financeFamily)
    {
        $form = $this
            ->formFactory
            ->createNamed(
                'finance_form',
                FinanceFamilyType::class,
                $financeFamily,
            )
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data['@id'] = $financeFamily['@id'];
                unset($data['factories'], $data['pricings']);
                $this->client->save('finance/finance_families', $data);
                $this->addFlash(
                    'success',
                    $this->translator->trans('finance.finance_family.edit.success', [], 'finance')
                );

                return $this->redirectToRoute('finance_family_show', ['id' => $financeFamily['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'financeFamily' => $financeFamily,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/finance-families/{id}/delete', name: 'finance_family_delete', methods: ['GET|DELETE'])]
    #[Template('finance/finance-families/edit.html.twig')]
    #[IsGranted('FEATURE_FINANCE_FAMILY_ADMIN')]
    public function delete(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'finance/finance_families'])] ApiData $financeFamily)
    {
        if ($request->query->has('_token') && !$this->isCsrfTokenValid('delete', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete Finance Family: please refresh your page.');
        }

        try {
            $this->client->remove('finance/finance_families', $financeFamily['id']);

            $this->addFlash(
                'success',
                $this->translator->trans('finance.finance_family.delete.success', [], 'finance')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('finance.finance_family.delete.error', [], 'finance')
            );
        }

        return $this->redirectToRoute('finance_family_home');
    }
}

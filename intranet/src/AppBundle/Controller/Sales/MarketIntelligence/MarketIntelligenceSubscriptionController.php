<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\MarketIntelligence;

use ApiBundle\Client;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/market-intelligence-subscriptions', defaults: ['alvest_module' => 'MIM', 'moduleDomain' => 'market_intelligence'])]
class MarketIntelligenceSubscriptionController extends AbstractController
{
    final public const RESOURCE_URL = 'sales/market_intelligence_subscriptions';

    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), Client::class, TranslatorInterface::class];
    }

    #[Route(path: '', name: 'market_intelligence_subscription_home', defaults: ['label' => 'subscriptions.my_subscriptions'])]
    #[Template('sales/market_intelligence_subscriptions/home.html.twig')]
    public function home(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] HydraCollection $marketIntelligenceSubscriptions)
    {
        return ['marketIntelligenceSubscriptions' => $marketIntelligenceSubscriptions];
    }

    #[Route(path: '/{id}/delete', name: 'market_intelligence_subscription_delete', methods: ['GET|DELETE'])]
    public function deleteSubscription(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $marketIntelligenceSubscription, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_subscription', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete role: please refresh your page.');
        }

        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $marketIntelligenceSubscription['id']);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('subscriptions.success.delete', [], 'market_intelligence')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('subscriptions.failure.delete', [], 'market_intelligence')
            );
        }

        return $this->redirectToRoute('market_intelligence_subscription_home');
    }

    #[Route(path: '/add', name: 'market_intelligence_subscription_add', defaults: ['label' => 'subscriptions.add_subscription'])]
    #[Template('sales/market_intelligence_subscriptions/add.html.twig')]
    public function add()
    {
        $client = $this->container->get(Client::class);
        $productTypes = $client->findBy('sales/product_types', ['normalization_groups_override' => ['catalogue_type_list']], ['englishName' => 'asc'], ['raw_results' => true]);
        $marketIntelligenceTypes = $client->findBy('sales/market_intelligence_types', [], ['name' => 'ASC'], ['raw_results' => true]);

        return [
            'initialState' => [
                'productType' => [
                    'productTypes' => $productTypes['hydra:member'],
                ],
                'marketIntelligence' => [
                    'marketIntelligenceTypes' => $marketIntelligenceTypes['hydra:member'],
                ],
            ],
        ];
    }
}

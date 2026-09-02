<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\MarketIntelligence;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Sales\MarketIntelligence\MarketIntelligenceTypeType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/market-intelligence-types', defaults: ['alvest_module' => 'MIM', 'moduleDomain' => 'market_intelligence'])]
class MarketIntelligenceTypeController extends AbstractController
{
    final public const RESOURCE_URL = 'sales/market_intelligence_types';

    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), Client::class, TranslatorInterface::class, ViolationMapper::class];
    }

    #[Route(path: '', name: 'market_intelligence_type_home', defaults: ['label' => 'market_intelligence.types.title'])]
    #[Template('sales/market_intelligence_type/home.html.twig')]
    public function home(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'filter' => ['order' => ['name' => 'ASC']]])] HydraCollection $marketIntelligenceTypes)
    {
        return ['marketIntelligenceTypes' => $marketIntelligenceTypes];
    }

    #[Route(path: '/add', name: 'market_intelligence_type_add', defaults: ['label' => 'market_intelligence.types.add'])]
    #[Template('sales/market_intelligence_type/add.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_MARKET_INTELLIGENCE_TYPE_WRITE') or is_granted('MOO_MIM')"))]
    public function add(Request $request)
    {
        $form = $this->createForm(MarketIntelligenceTypeType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('market_intelligence.types.success.add', [], 'market_intelligence')
                );

                return $this->redirectToRoute('market_intelligence_type_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}

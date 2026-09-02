<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Quality\NonConformity\NonQualityCostType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/quality/non-quality-costs', defaults: ['alvest_module' => 'NCR', 'moduleDomain' => 'non_conformity'])]
class NonQualityCostController extends AbstractController
{
    final public const NON_QUALITY_COST_URL = 'quality/non_quality_costs';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, FormFactoryInterface::class, ViolationMapper::class, TranslatorInterface::class]);
    }

    #[Route(path: '', name: 'non_quality_cost_home', methods: ['GET|POST'], defaults: ['label' => 'non_conformity.non_quality_cost.title'])]
    #[Template('quality/non_quality_cost/home.html.twig')]
    public function home(#[ApiValueResolverAttribute(parameters: ['resource' => self::NON_QUALITY_COST_URL, 'filters' => ['order' => ['location.name' => 'ASC']]])] HydraCollection $nonQualityCosts, Request $request)
    {
        $client = $this->container->get(Client::class);
        $locations = $client->get('locations', ['query' => ['has_any_capability' => ['factory', 'sso']]]);
        $formFactory = $this->container->get(FormFactoryInterface::class);
        $forms = [];
        foreach ($locations['hydra:member'] as $location) {
            $payload = ['location' => $location['@id']];
            foreach ($nonQualityCosts as $nonQualityCost) {
                $locationProperty = $nonQualityCost['location']['@id'] ?? $nonQualityCost['location'];
                if ($location['@id'] === $locationProperty) {
                    $payload = [
                        '@id' => $nonQualityCost['@id'],
                        'location' => $locationProperty,
                        'defaultCosts' => $nonQualityCost['defaultCosts'],
                    ];
                    break;
                }
            }

            $forms[] = $form = $formFactory->createNamed(\sprintf('%d_form', $location['id']), NonQualityCostType::class, $payload)->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $client->save(self::NON_QUALITY_COST_URL, $form->getData());
                } catch (ClientException $e) {
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                }
                $this->redirectToRoute('non_quality_cost_home');
            }
        }

        return [
            'editable' => $this->isGranted('FEATURE_NON_QUALITY_COSTS_ADMIN'),
            'forms' => array_map(static fn (FormInterface $form) => $form->createView(), $forms),
            'nonQualityCosts' => $nonQualityCosts,
        ];
    }
}

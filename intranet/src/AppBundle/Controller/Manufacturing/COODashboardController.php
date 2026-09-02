<?php

declare(strict_types=1);

namespace AppBundle\Controller\Manufacturing;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use AppBundle\Filters\Type\Quality\SmwFilterType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class COODashboardController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/manufacturing/coo-dashboard', name: 'coo_dashboard', methods: ['GET|POST'])]
    #[Template('manufacturing/coo_dashboard/show.html.twig')]
    public function showDashboard(Request $request)
    {
        $lastMonth = (new \DateTime('last month'));
        $user = $this->client->get('/me');
        $form = $this->createForm(SmwFilterType::class, [], [
            'method' => 'GET',
        ]);
        $location = $this->client->findOneBy('locations', ['legacyId' => $user['businessUnit']['legacyId']]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('location')->getData();

            try {
                $location = $this->client->find('locations', Iri::id($data));
            } catch (\Exception $e) {
                return 'Could not get Location';
            }
        }

        return [
            'lastMonth' => $lastMonth,
            'user' => $user,
            'location' => $location,
            'form' => $form->createView(),
        ];
    }
}

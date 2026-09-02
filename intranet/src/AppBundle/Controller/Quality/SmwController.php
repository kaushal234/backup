<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use AppBundle\Filters\Type\Quality\SmwFilterType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['alvest_module' => 'SMW'])]
class SmwController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/quality/smw-meeting', name: 'smw_meeting_quality', methods: ['GET|POST'])]
    #[Template('quality/smw/show.html.twig')]
    public function indexQuality(Request $request)
    {
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
            'user' => $user,
            'location' => $location,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/manufacturing/smw-meeting', name: 'smw_meeting_manufacturing', methods: ['GET|POST'])]
    #[Template('manufacturing/smw/show.html.twig')]
    public function indexProduction(Request $request)
    {
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
            'user' => $user,
            'location' => $location,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/purchasing/smw-meeting', name: 'smw_meeting_purchasing', methods: ['GET|POST'])]
    #[Template('purchasing/smw/show.html.twig')]
    public function indexPurchasing(Request $request)
    {
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
            'user' => $user,
            'location' => $location,
            'form' => $form->createView(),
        ];
    }
}

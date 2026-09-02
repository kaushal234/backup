<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use AppBundle\Filters\Type\Quality\SmwFilterType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['alvest_module' => 'SMW'])]
class SmwController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/{module}/smw-meeting', requirements: ['module' => 'engineering|purchasing|manufacturing|quality|support|service'], name: 'smw_meeting', methods: ['GET|POST'])]
    #[Template('purchasing/smw/show.html.twig')]
    public function indexAction(Request $request, string $module)
    {
        $user = $this->client->get('/me');
        $form = $this->createForm(SmwFilterType::class, [], [
            'method' => 'GET',
            'type' => 'service' === $module ? 'sso' : 'factory',
        ]);
        $location = $this->client->findOneBy('locations', ['legacyId' => $user['businessUnit']['legacyId']]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $location = $this->client->find('locations', Iri::id($form->get('location')->getData()));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Could not get Location');
            }
        }

        switch ($module) {
            case 'engineering':
                $template = 'engineering/smw/show.html.twig';
                break;
            case 'purchasing':
                $template = 'purchasing/smw/show.html.twig';
                break;
            case 'manufacturing':
                $template = 'manufacturing/smw/show.html.twig';
                break;
            case 'quality':
                $template = 'quality/smw/show.html.twig';
                break;
            case 'support':
                $template = 'support/smw/show.html.twig';
                break;
            case 'service':
                $template = 'service/smw/show.html.twig';
                break;
            default:
                throw new NotFoundHttpException();
        }

        return $this->render($template, [
            'user' => $user,
            'location' => $location,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/purchasing/smw-meeting/charts', name: 'smw_charts', methods: ['GET'])]
    #[Template('purchasing/smw/charts.html.twig')]
    public function charts()
    {
    }
}

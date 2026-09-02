<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality\CalibratedTools;

use ApiBundle\Client;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/quality/calibrated-tools/dashboard', defaults: ['alvest_module' => 'CT'])]
class DashboardController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '', name: 'quality_dashboard', methods: 'GET')]
    #[Template('quality\dashboard\index.html.twig')]
    public function index()
    {
        $user = $this->client->get('me');

        $statusByBusinessUnit = $this->client->get('reports/resource=/quality/calibrated_tools/tools;x=status;y=locationArea.factory.name');

        $options = ['options' => []];
        if (isset($user['businessUnit']['location']['@id']) && $this->isGranted('LOCATION_FACTORY')) {
            $options['options']['location'] = $user['businessUnit']['location']['@id'];
        }

        $statusByLocationArea = $this->client->get('reports/resource=/quality/calibrated_tools/tools;x=locationArea.name;y=status', [
            'query' => $options,
        ]);

        return [
            'statusByBusinessUnit' => $statusByBusinessUnit,
            'statusByLocationArea' => $statusByLocationArea,
        ];
    }
}

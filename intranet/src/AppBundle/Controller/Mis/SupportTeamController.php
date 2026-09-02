<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Mis\SupportTeamDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mis/support-teams', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'support_team'])]
class SupportTeamController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    /** @var string */
    public const RESOURCE_URL = 'mis/support_teams';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
        ]);
    }

    #[Route(name: 'support_team_home', methods: ['GET|POST'])]
    #[Template('mis/support_team/home.html.twig')]
    public function home(Request $request)
    {
        $datatable = $this->createDataTable(SupportTeamDataTableType::class, SupportTeamDataTableType::RESOURCE);
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'supportTeamDataTable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/add', name: 'support_team_add', methods: ['GET|POST'])]
    #[Route(path: '/{id}/edit', name: 'support_team_edit', methods: ['GET|POST'])]
    #[Template('mis/support_team/write.html.twig')]
    public function write(Request $request, ?int $id = null)
    {
        $client = $this->container->get(Client::class);
        $supportTeam = null === $id ? null : $client->find(self::RESOURCE_URL, $id, ['raw_results' => true]);

        return [
            'supportTeam' => $supportTeam,
            'initialState' => [
                'mis' => [
                    'details' => null === $id ? [] : $supportTeam,
                ],
            ],
            'props' => [
                'formType' => null !== $id ? 'edit' : 'add',
            ],
        ];
    }

    #[Route(path: '/{id}/delete', name: 'support_team_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_DELETE_SUPPORT_TEAM')]
    public function delete(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $supportTeam)
    {
        if (!$this->isCsrfTokenValid('delete_support_team', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('delete.errors', [], 'file_type'));

            return $this->redirectToRoute('support_team_home');
        }
        $this->container->get(Client::class)->remove(self::RESOURCE_URL, $supportTeam->getIriId());

        return $this->redirectToRoute('support_team_home');
    }
}

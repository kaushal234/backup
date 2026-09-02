<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Directory\People\SearchType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/directory', defaults: ['alvest_module' => 'DIR'])]
class DirectoryController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '', name: 'directory_index', methods: 'GET')]
    #[Template('directory/index.html.twig')]
    public function index(#[ApiValueResolverAttribute(parameters: ['filters' => ['order' => ['name' => 'ASC'], 'normalizationGroups' => ['division:tree']]])] HydraCollection $divisions, Request $request)
    {
        $showDisabledBusinessUnits = ($this->isGranted('ACL_SUPERUSER') || $this->isGranted('ACL_GG_MIS'))
            && $request->query->get('showDisabledBusinessUnits', false);

        $form = $this->createForm(SearchType::class, [], [
            'action' => $this->generateUrl('directory_people_search'),
            'method' => 'GET',
            'csrf_protection' => false,
        ]);

        return [
            'form' => $form->createView(),
            'divisions' => $divisions,
            'showDisabledBusinessUnits' => $showDisabledBusinessUnits,
        ];
    }

    #[Route(path: '/organisation/{regionId}', name: 'directory_organisation_region', methods: 'GET', defaults: ['businessUnitId' => null], requirements: ['regionId' => '\d+'])]
    #[Route(path: '/organisation/{regionId}/{businessUnitId}', name: 'directory_organisation_business_unit', methods: 'GET', requirements: ['regionId' => '\d+', 'businessUnitId' => '\d+'])]
    #[Template('directory/organisation.html.twig')]
    public function organisation($regionId, $businessUnitId, Request $request)
    {
        $iri = null;
        if (empty($businessUnitId)) {
            $entity = $this->client->find('regions', $regionId);
        } else {
            $entity = $this->client->find('business_units', $businessUnitId);
        }

        $topPeople = null;
        if (null !== $entity['representative'] && isset($entity['representative']['@id']) && null !== $iri = Iri::id($entity['representative']['@id'])) {
            try {
                $topPeople = $this->client->find('people', $iri);
            } catch (ClientException $e) {
                // continue;
            }
        }

        if (null === $topPeople) {
            $this->addFlash('error', 'There are no representative for this region.');
            if ($referer = $request->headers->get('referer')) {
                return $this->redirect($request->headers->get('referer'));
            }

            return $this->redirectToRoute('directory_index');
        }

        $underPeople = $this->client->findBy('people', [
            'supervisor' => $iri,
            'hidden' => 0,
            'disabled' => 0,
            'normalization_groups' => ['group_member'],
        ]);

        return [
            'entity' => $entity,
            'topPeople' => $topPeople,
            'underPeople' => $underPeople,
        ];
    }

    #[Route(path: '/organisation/under/{peopleId}', name: 'directory_organisation_business_unit_people_ajax', methods: 'GET', requirements: ['peopleId' => '\d+'], condition: 'request.isXmlHttpRequest()', options: ['expose' => true])]
    #[Template('directory/_ajax_organisation.html.twig')]
    public function organisationAjax($peopleId)
    {
        $people = $this->client->findBy('people', [
            'supervisor' => $peopleId,
            'hidden' => 0,
            'disabled' => 0,
            'normalization_groups' => ['group_member'],
        ]);

        return [
            'peopleId' => $peopleId,
            'people' => $people,
        ];
    }
}

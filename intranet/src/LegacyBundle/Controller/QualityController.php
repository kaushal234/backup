<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use AppBundle\Controller\Quality\NonConformityController;
use LegacyBundle\Http\LegacyResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QualityController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/manufacturing/qa/dev.php', name: 'legacy_manufacturing_qa', methods: 'GET|POST')]
    public function router(Request $request): RedirectResponse
    {
        $m = $request->query->all()['m'] ?? [];
        $id = $request->query->get('id');

        if (!isset($m[0])) {
            throw $this->createNotFoundException();
        }

        if (0 !== \count($m)) {
            switch ($m[0]) {
                case 'ncr':
                    if (empty($m[1])) {
                        return $this->redirectToRoute('non_conformity_home');
                    }
                    switch ($m[1]) {
                        case 'reports':
                            return $this->redirectToRoute('non_conformity_report');
                        case 'forms':
                            if (empty($m[2])) {
                                return $this->redirectToRoute('non_conformity_list');
                            }
                            switch ($m[2]) {
                                case 'parts.getinfo':
                                case 'addParts':
                                case 'newNCR':
                                    return $this->redirectToRoute('non_conformity_add');
                                case 'byID':
                                    return $this->redirectToRoute('non_conformity_home');
                                case 'changeStatus':
                                    try {
                                        $nonConformity = $this->client->find(NonConformityController::NON_CONFORMITY_URL, $id);
                                    } catch (\RangeException $e) {
                                        throw new LegacyResourceNotFoundException();
                                    }

                                    return $this->redirectToRoute('non_conformity_show', ['id' => Iri::id($nonConformity)]);
                            }
                            // no break
                        case 'view':
                            try {
                                $nonConformity = $this->client->find(NonConformityController::NON_CONFORMITY_URL, $id);
                            } catch (\RangeException $e) {
                                throw new LegacyResourceNotFoundException();
                            }

                            if (empty($m[2])) {
                                return $this->redirectToRoute('non_conformity_show', ['id' => Iri::id($nonConformity)]);
                            }
                            switch ($m[2]) {
                                case 'parts':
                                    return $this->redirectToRoute('non_conformity_admin_parts', ['id' => Iri::id($nonConformity)]);
                                case 'edit':
                                case 'selectVendor':
                                case 'txVendor':
                                    return $this->redirectToRoute('non_conformity_edit', ['id' => Iri::id($nonConformity)]);
                                case 'log':
                                case 'print':
                                case 'links':
                                case 'files':
                                case 'costs':
                                case 'models':
                                case 'tasks':
                                case 'car':
                                case 'changeStatus':
                                    return $this->redirectToRoute('non_conformity_show', ['id' => Iri::id($nonConformity)]);
                            }
                    }
                    break;
                case 'cpa':
                    $request->attributes->set('alvest_module', 'CPA');
                    break;
                case 'scar':
                    if (empty($m[1])) {
                        return $this->redirectToRoute('supplier_corrective_action_request_home');
                    }
                    switch ($m[1]) {
                        case 'view':
                            if (!empty($m[2]) && 'edit' === $m[2]) {
                                return $this->redirectToRoute('supplier_corrective_action_request_edit', ['id' => $id]);
                            }

                            return $this->redirectToRoute('supplier_corrective_action_request_show', ['id' => $id]);
                        case 'add':
                            return $this->redirectToRoute('supplier_corrective_action_request_add');
                    }

                    return $this->redirectToRoute('supplier_corrective_action_request_home');
                case 'gt':
                    $request->attributes->set('alvest_module', 'ODP');
                    break;
                case 'crab':
                    if (empty($m[1])) {
                        return $this->redirectToRoute('crab_home');
                    }
                    switch ($m[1]) {
                        case 'graph':
                        case 'reports':
                        case 'report':
                        case 'fullReport':
                            return $this->redirectToRoute('crab_report');
                        case 'listing':
                            return $this->redirectToRoute('crab_home');
                        case 'form':
                            if (empty($m[2])) {
                                return $this->redirectToRoute('crab_home');
                            }
                            switch ($m[2]) {
                                case 'newCRABPDI':
                                case 'newCRAB':
                                case 'newCRAB1':
                                    return $this->redirectToRoute('crab_add');
                                case 'byNum':
                                case 'byNCR':
                                case 'search':
                                    return $this->redirectToRoute('crab_home');
                            }
                            break;
                        case 'view':
                            try {
                                $crab = $this->client->findOneBy('quality/crabs', ['legacyId' => $id]);
                            } catch (\RangeException $e) {
                                throw new LegacyResourceNotFoundException();
                            }

                            if (empty($m[2])) {
                                return $this->redirectToRoute('crab_show', ['id' => Iri::id($crab)]);
                            }
                            switch ($m[2]) {
                                case 'duplicate':
                                    return $this->redirectToRoute('crab_duplicate', ['id' => Iri::id($crab)]);
                                case 'transfer':
                                case 'filteringFlag':
                                case 'inspected':
                                case 'tasks':
                                case 'log':
                                case 'files':
                                case 'links':
                                    return $this->redirectToRoute('crab_show', ['id' => Iri::id($crab)]);
                            }
                    }
                    break;
            }
        }

        throw $this->createNotFoundException();
    }

    #[Route(path: '/manufacturing/qa/scar/scar_admin.php', name: 'legacy_scar_admin', methods: 'GET|POST')]
    public function routerScarAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('supplier_corrective_action_request_home');
    }

    #[Route(path: '/manufacturing/qa/ncr/ncr_admin.php', name: 'legacy_ncr_admin', methods: 'GET|POST')]
    public function routerNcrAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('non_conformity_home');
    }

    #[Route(path: '/manufacturing/qa/crab/crab_admin.php', name: 'legacy_crab_admin', methods: 'GET|POST')]
    public function routerCrabAdmin()
    {
        return $this->redirectToRoute('crab_home');
    }
}

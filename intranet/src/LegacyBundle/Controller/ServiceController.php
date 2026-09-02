<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use LegacyBundle\Http\LegacyResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    #[Route(path: '/sales_service/service.php', name: 'legacy_service', methods: 'GET|POST')]
    public function router(Request $request)
    {
        $m = $request->query->all()['m'] ?? [];

        if (!empty($m)) {
            switch ($m[0]) {
                case 'toc':
                    $request->attributes->set('alvest_module', 'TOC');

                    switch ($m[1] ?? null) {
                        case 'listing':
                            return $this->redirectToRoute('technician_on_call_search');
                        case 'reports':
                            return $this->redirectToRoute('technician_on_calls_reports');
                        case 'kpi':
                            return $this->redirectToRoute('technician_on_calls_kpi');
                        case 'form':
                            switch ($m[2] ?? null) {
                                case 'new':
                                case 'new2':
                                case 'new3':
                                case 'new4':
                                    return $this->redirectToRoute('technician_on_calls_write');
                                case 'newLink':
                                    break;
                                default:
                                    return $this->redirectToRoute('technician_on_call_search');
                            }
                            break;
                        case 'view':
                            if (null !== $id = $request->query->get('id')) {
                                return $this->redirectToRoute('technician_on_calls_show', ['id' => $id]);
                            }
                            // no break
                        default:
                            return $this->redirectToRoute('technician_on_calls_home');
                    }
                    // no break
                case 'csr':
                    if (empty($m[1])) {
                        return $this->redirectToRoute('customer_service_record_home');
                    }

                    $legacyAllowedReports = [
                        'costByCriteria',
                        'SBCostByCriteria',
                    ];

                    switch ($m[1]) {
                        case 'reports':
                            if (isset($m[2]) && \in_array($m[2], $legacyAllowedReports, true)) {
                                break;
                            }

                            return $this->redirectToRoute('customer_service_record_report');
                        case 'listing':
                            if (isset($m[2]) && \in_array($m[2], $legacyAllowedReports, true)) {
                                break;
                            }

                            return $this->redirectToRoute('customer_service_record_search');
                        case 'forms':
                            switch ($m[2] ?? null) {
                                case 'add':
                                    return $this->redirectToRoute('customer_service_record_add', [
                                        'equipmentRecord' => $request->query->get('equipmentRecord'),
                                        'type' => $request->query->get('type'),
                                    ]);
                                case 'byNum':
                                case 'byOldCSR':
                                case 'bySR':
                                case 'add2':
                                    return $this->redirectToRoute('customer_service_record_search');
                            }

                            return $this->redirectToRoute('customer_service_record_search');
                        case 'view':
                            $id = $request->query->get('id') ?? $request->request->get('id');
                            try {
                                $customerServiceRecord = $this->client->findOneBy('service/customer_service_records', ['legacyId' => $id]);
                            } catch (\RangeException $e) {
                                throw new LegacyResourceNotFoundException();
                            }

                            if (empty($m[2])) {
                                return $this->redirectToRoute('customer_service_record_show', ['id' => Iri::id($customerServiceRecord)]);
                            }

                            switch ($m[2]) {
                                case 'edit':
                                case 'costs':
                                    // Keep the legacy page
                                    throw $this->createNotFoundException();
                                case 'survey':
                                case 'duplicate':
                                case 'members':
                                case 'status':
                                case 'tasks':
                                case 'log':
                                case 'files':
                                case 'links':
                                case 'parts':
                                case 'faq':
                                case 'labors':
                                    return $this->redirectToRoute('customer_service_record_show', ['id' => Iri::id($customerServiceRecord)]);
                                case 'delete':
                                    return $this->redirectToRoute('customer_service_record_edit', ['id' => Iri::id($customerServiceRecord)]);
                            }
                    }
                    // no break
                case 'scm':
                    $request->attributes->set('alvest_module', 'SCM');
                    break;
            }
        }

        throw $this->createNotFoundException();
    }
}

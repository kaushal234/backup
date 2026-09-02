<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EngineeringController extends AbstractController
{
    #[Route(path: '/manufacturing/eng/dev.php', name: 'legacy_manufacturing_eng', methods: 'GET|POST')]
    public function router(Request $request): RedirectResponse
    {
        $m = $request->query->all()['m'] ?? [];

        if (!empty($m)) {
            switch ($m[0]) {
                case 'reports':
                    $request->attributes->set('alvest_module', 'MR');
                    break;
                case 'meap':
                    $request->attributes->set('alvest_module', 'MEAP');
                    break;
                case 'eap':
                    $request->attributes->set('alvest_module', 'EAP');
                    break;
                case 'pip':
                    $request->attributes->set('alvest_module', 'PIP');
                    break;
                case 'timekeeping':
                    $request->attributes->set('alvest_module', 'TIME');
                    break;
                case 'bom':
                    if (['view', 'ZipAllDiagram'] === [$m[1] ?? null, $m[2] ?? null] && $request->query->has('pn') && $request->query->has('erp')) {
                        $request->attributes->set('alvest_module', 'ER');

                        return $this->redirectToRoute('manufacturing_engineering_zip', [
                            'site' => (int) $request->query->get('erp'),
                            'item' => $request->query->get('pn'),
                            'date' => $request->query->get('date'),
                            'flat' => $request->query->get('flat'),
                            'formats' => $request->query->all('formats'),
                        ]);
                    }
                    break;
                case 'cbom':
                    $request->attributes->set('alvest_module', 'ER');
                    break;
                case 'edm':
                    return $this->redirect('/en/private/manufacturing/eng/dev.php?m[0]=revisions_detail');
                case 'getfile':
                    switch ($m[1]) {
                        case 'drawing':
                            break;
                    }
            }
        }

        throw $this->createNotFoundException();
    }
}

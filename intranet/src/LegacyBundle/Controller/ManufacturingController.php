<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ManufacturingController extends AbstractController
{
    #[Route(path: '/manufacturing/index.php', name: 'legacy_manufacturing_index')]
    public function router(Request $request): RedirectResponse
    {
        $m = $request->query->all()['m'] ?? [];

        if (!empty($m)) {
            switch ($m[0]) {
                case 'reports':
                    throw $this->createNotFoundException();
                case 'kpi':
                case 'kpi2':
                    $request->attributes->set('alvest_module', 'MR');
                    break;
                case 'planning':
                    $request->attributes->set('alvest_module', 'PURR');
                    break;
                case 'pi':
                    $request->attributes->set('alvest_module', 'PI');
                    if ('timekeeping' === ($m[1] ?? null)) {
                        return $this->redirectToRoute('timekeeping_submit_close_transaction');
                    }
                    if ('questionsByModelAndFactory' === ($m[1] ?? null)) {
                        return $this->redirectToRoute('legacy_manufacturing_index', [
                            'm' => ['pi', 'reports', 'questionsByModelAndFactory'],
                        ]);
                    }
                    break;
                case 'activity':
                    $request->attributes->set('alvest_module', 'ACT');
                    break;
            }
        }

        throw $this->createNotFoundException();
    }
}

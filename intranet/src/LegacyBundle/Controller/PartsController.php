<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Migration HACK.
 *
 * We catch the legacy url and forward to our controller only for migrated pages.
 * Otherwise, throw NotFoundException to fallback on the legacy.
 */
class PartsController extends AbstractController
{
    #[Route(path: '/parts/parts.php', name: 'legacy_parts_parts', methods: 'GET|POST', defaults: ['alvest_module' => 'SPH'])]
    public function router(Request $request): RedirectResponse
    {
        if ($request->request->has('m')) {
            throw $this->createNotFoundException();
        }

        $m = $request->query->all()['m'] ?? [];

        if (isset($m[0])) {
            switch ($m[0]) {
                case 'spr':
                    $request->attributes->set('alvest_module', 'SPR');
                    break;
                case 'spq':
                    $request->attributes->set('alvest_module', 'SPQ');
                    break;
                case 'inv':
                    if ('view' === $m[1]) {
                        $id = $request->query->get('id');
                        if ($id) {
                            return $this->redirectToRoute('parts_dashboard_view', ['partNumber' => $id]);
                        }

                        return $this->redirectToRoute('parts_dashboard_home');
                    }
                    // no break
                case 'dino_trno':
                    return $this->redirectToRoute('home');
            }
        }
        /*
         * SPQ1 creation
         */
        if ($m === ['spq', 'form', 'create'] || $m === ['spq', 'form', 'create2']) {
            return $this->redirectToRoute('spq_quotations_home');
        }

        throw $this->createNotFoundException();
    }

    #[Route(path: '/parts/dino_trno/dino_trno_admin.php', name: 'legacy_parts_dino_admin', methods: 'GET|POST', defaults: ['alvest_module' => 'SPH'])]
    public function dinoAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('home');
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use LegacyBundle\Http\LegacyResourceNotFoundException;
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
class CommonController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/common/index.php', name: 'legacy_common', methods: 'GET|POST')]
    public function router(Request $request): RedirectResponse
    {
        $m = $request->query->all()['m'] ?? [];
        $module = $request->query->get('module');
        $id = $request->query->get('parent_id');

        if ($m === ['files', 'form', 'newFile'] && 'COR' === $module) {
            try {
                $competitor = $this->client->findOneBy('sales/competitors', ['legacyId' => $id]);
            } catch (\RangeException $e) {
                throw new LegacyResourceNotFoundException();
            }

            return $this->redirectToRoute('sales_competitors_files', ['id' => $competitor['id']]);
        }

        if ($m === ['models', 'new'] && 'NCR' === $module) {
            return $this->redirectToRoute('non_conformity_home');
        }

        throw $this->createNotFoundException();
    }
}

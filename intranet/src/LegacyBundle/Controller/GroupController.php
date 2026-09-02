<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use LegacyBundle\Http\LegacyResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Migration HACK.
     *
     * URLs in module 'grdesc_admin.php' are auto-generated
     * by shared file 'db_admin2.inc'.
     * So we catch the legacy url and forward to our controller
     * only for migrated 'mode'.
     * Otherwise, throw NotFoundException to fallback on the legacy.
     */
    #[Route(path: '/mis/grdesc/grdesc_admin.php', name: 'legacy_mis_grdesc_admin', methods: 'GET|POST')]
    public function router(Request $request): RedirectResponse
    {
        switch ($request->query->get('mode')) {
            case 'record_view':
                try {
                    $group = $this->client->findOneBy('groups', ['legacyId' => $request->query->get('id')]);
                } catch (\RangeException $e) {
                    throw new LegacyResourceNotFoundException();
                }

                return $this->redirectToRoute('mis_groups_show', [
                    'id' => Iri::id($group),
                ]);
            case 'duplicate':
            case 'form_add':
                return $this->redirectToRoute('mis_groups_add');
            case 'form_edit':
                try {
                    $group = $this->client->findOneBy('groups', ['legacyId' => $request->query->get('id')]);
                } catch (\RangeException $e) {
                    throw new LegacyResourceNotFoundException();
                }

                return $this->redirectToRoute('mis_groups_edit', [
                    'id' => Iri::id($group),
                ]);
            case 'del':
                try {
                    $group = $this->client->findOneBy('groups', ['legacyId' => $request->query->get('id')]);
                } catch (\RangeException $e) {
                    throw new LegacyResourceNotFoundException();
                }

                return $this->redirectToRoute('mis_groups_delete', [
                    'id' => Iri::id($group),
                ]);
        }

        throw $this->createNotFoundException();
    }
}

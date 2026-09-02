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

class ModuleAdminController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/module/module_admin.php', name: 'legacy_module_admin', methods: 'GET|POST')]
    public function router(Request $request): RedirectResponse
    {
        $mode = $request->query->get('mode');
        $formType = $request->query->get('form_type');
        $id = $request->query->get('id');

        if ('main_tpl' !== $formType || null === $id) {
            throw $this->createNotFoundException();
        }

        try {
            $module = $this->client->findOneBy('modules', ['legacyId' => $id]);
        } catch (\RangeException $e) {
            throw new LegacyResourceNotFoundException();
        }

        switch ($mode) {
            case 'record_view':
            case 'del':
                return $this->redirectToRoute('mis_groups_show', ['id' => Iri::id($module)]);
            case 'form_edit':
                return $this->redirectToRoute('mis_groups_edit', ['id' => Iri::id($module)]);
        }

        throw $this->createNotFoundException();
    }
}

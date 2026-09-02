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

/**
 * Migration HACK.
 *
 * We catch the legacy url and forward to our controller only for migrated pages.
 * Otherwise, throw NotFoundException to fallback on the legacy.
 */
class CorporateController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/corporate/index.php', name: 'legacy_corporate_index', methods: 'GET|POST')]
    public function router(Request $request): RedirectResponse
    {
        $m = $request->query->all()['m'] ?? [];

        switch ($m[0] ?? null) {
            case 'agr':
                if (empty($m[1])) {
                    return $this->redirectToRoute('acronyms_home');
                }

                switch ($m[1]) {
                    case 'form':
                        if (isset($m[2]) && 'new' === $m[2]) {
                            return $this->redirectToRoute('acronyms_add');
                        }
                        break;
                    case 'listing':
                        if (isset($m[2]) && 'search' === $m[2]) {
                            return $this->redirectToRoute('acronyms_search');
                        }
                        break;
                    case 'view':
                        try {
                            $acronym = $this->client->findOneBy('acronyms', ['legacyId' => $request->query->get('id')]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        return $this->redirectToRoute('acronyms_show', ['id' => Iri::id($acronym)]);
                    case 'xls':
                        return $this->redirectToRoute('acronyms_home');
                }

                break;
            case null:
                return $this->redirectToRoute('corporate_pages_main');
        }

        throw $this->createNotFoundException();
    }

    #[Route(path: '/corporate/agr/agr_admin.php', name: 'legacy_corporate_agr_admin', methods: 'GET|POST')]
    public function agrAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('acronyms_home');
    }
}

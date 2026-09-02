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

class NewsController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Migration HACK.
     */
    #[Route(path: '/internal_news/internal_news.php', name: 'legacy_news_admin', methods: 'GET|POST')]
    public function router(Request $request): RedirectResponse
    {
        if ('form_add' === $mode = $request->query->get('mode')) {
            return $this->redirectToRoute('news_add');
        }

        $news = false;
        if (null !== $id = $request->query->get('id')) {
            try {
                $news = $this->client->findOneBy('news', ['legacyId' => $id]);
            } catch (\RangeException $e) {
                throw new LegacyResourceNotFoundException();
            }
        }

        switch ($mode) {
            case 'record_view':
                return $this->redirectToRoute('news_show', ['id' => Iri::id($news)]);
            case 'form_edit':
                return $this->redirectToRoute('news_edit', ['id' => Iri::id($news)]);
            case 'del':
                return $this->redirectToRoute('news_delete', ['id' => Iri::id($news)]);
            default:
                return $this->redirectToRoute('news_index');
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Chart\Purchasing\SupplierRanking\RankingRadarChartBuilder;
use App\CQRS\Query\FindAllRepresentativeQuery;
use App\CQRS\Query\News\FindCurrentEvendorsNewsQuery;
use App\CQRS\Query\SupplierRanking\FindSupplierRankingsByBusinessPartnerCodesQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Downloader;
use App\Sdk\Resource\EvendorsNewsFile;
use App\Security\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly Responder $responder,
        private readonly QueryBusInterface $bus,
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/', name: 'index', methods: [Request::METHOD_GET])]
    public function __invoke(): Response
    {
        if (!$this->security->isFullyAuthenticated()) {
            return $this->responder->route('security:login');
        }

        $user = $this->security->getAuthenticatedUser();
        $representatives = $this->bus->dispatch(new FindAllRepresentativeQuery());

        $supplierRankings = [];
        try {
            $supplierRankings = $this->bus->dispatch(new FindSupplierRankingsByBusinessPartnerCodesQuery());
            $news = $this->bus->dispatch(new FindCurrentEvendorsNewsQuery());
        } catch (HandlerFailedException $e) {
        }

        $charts = [];
        foreach ($supplierRankings as $supplierRanking) {
            $chartBuilder = new RankingRadarChartBuilder($supplierRanking->notations);
            $chartBuilder->setTitle($supplierRanking->supplier->name);
            $charts[] = [
                'supplierRanking' => $supplierRanking,
                'chart' => $chartBuilder->buildConfig(),
            ];
        }

        return $this->responder->render('index.html.twig', [
            'user' => $user,
            'representatives' => $representatives,
            'evendorsNews' => $news ?? [],
            'supplierRankingCharts' => $charts,
        ]);
    }

    #[Route('/evendors_news/{id}/show-file/{fileId}', name: 'evendors_news_show_file', methods: [Request::METHOD_GET])]
    public function showFile(int $id, int $fileId): StreamedResponse
    {
        return $this->downloader->stream(EvendorsNewsFile::class, ['resource_id' => $id, 'file_id' => $fileId]);
    }
}

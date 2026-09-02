<?php

declare(strict_types=1);

namespace App\Controller\Manual;

use App\CQRS\Query\Manual\FindManualQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Resource\Manual;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/manuals')]
class ShowController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route(path: '/{id}', name: 'manual:show', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, Request $request): Response
    {
        /** @var Manual $manual */
        $manual = $this->queryBus->dispatch(new FindManualQuery(id: $id));

        $documents = [];
        $categoriesOrder =
            [
                'Chapter 0',
                'Chapter 1',
                'Chapter 2',
                'Chapter 3',
                'Chapter 5',
                'Accessories and Options',
                'Body-Chassis',
                'Boom',
                'Bridge',
                'Covers and Panels',
                'Electrical System',
                'Elevator',
                'Hydraulic System',
                'Lifting-Scissors System',
                'Power Plant',
                'Suspension, Tires and Brakes',
                'User Interfaces and Cab',
                'PLUMBING',
                'Pneumatic System',
                'Refrigeration System',
                'TRANSMISSION',
                'No Category',
            ];
        foreach ($categoriesOrder as $category) {
            $documents[$category] = [];
        }

        foreach ($manual->documents as $document) {
            $key = null !== $document->category ? $document->category->name : 'No Category';
            $documents[$key][] = $document;
        }

        return $this->responder->render('manual/show.html.twig',
            [
                'manual' => $manual,
                'documents' => array_filter($documents),
            ]
        );
    }
}

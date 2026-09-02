<?php

declare(strict_types=1);

namespace App\Controller\TechnicianOnCall;

use App\CQRS\Query\Activity\FindAllCommentQuery;
use App\CQRS\Query\TechnicianOnCall\FindTechnicianOnCallQuery;
use App\CQRS\QueryBusInterface;
use App\DataTable\Type\Activity\CommentDataTableType;
use App\Http\Responder;
use App\Sdk\Resource\TechnicianOnCall;
use App\Security\Voter\TechnicianOnCallVoter;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsController]
#[Route(path: '/technician-on-calls')]
class ShowController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    #[Route(path: '/{id}', name: 'technician_on_call:show', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(int $id, Request $request): Response
    {
        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $this->queryBus->dispatch(new FindTechnicianOnCallQuery($id));

        $canComment = $this->authorizationChecker->isGranted(TechnicianOnCallVoter::COMMENT);

        $commentQuery = new FindAllCommentQuery(options: ['resource' => $technicianOnCall->iri]);
        $commentDataTable = $this->createDataTable(CommentDataTableType::class, $commentQuery);

        $commentDataTable->handleRequest($request);
        if ($commentDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($commentDataTable);
        }

        return $this->responder->render('technician_on_call/show.html.twig', [
            'technicianOnCall' => $technicianOnCall,
            'commentDataTable' => $commentDataTable->createView(),
            'canComment' => $canComment,
        ]);
    }
}

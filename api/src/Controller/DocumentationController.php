<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Symfony\Action\DocumentationAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentationController extends AbstractController
{
    public function __construct(private readonly DocumentationAction $documentationAction)
    {
    }

    public function __invoke(Request $request): Response
    {
        // force the Re_doc documentation format, otherwise on swagger format all schemas are displayed
        $request->query->set('ui', 're_doc');
        $action = $this->documentationAction;

        return $action($request);
    }
}

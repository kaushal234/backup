<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use ApiBundle\Http\FileStreamedResponseFactory;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/translator_document')]
class TranslatorController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [FileStreamedResponseFactory::class]);
    }

    #[Route('', name: 'translator_documents', methods: ['GET'])]
    #[Template('translator/translator_documents.html.twig')]
    public function home(): array
    {
        return [];
    }

    #[Route('/{id}/download', name: 'translator_document_download', methods: ['GET'])]
    public function download(int $id)
    {
        try {
            $file = $this->container->get(FileStreamedResponseFactory::class)->create(
                \sprintf('document_translations/%d/download', $id)
            );
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('translator_documents');
        }

        return $file;
    }
}

<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Manager\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActivityController extends AbstractController
{
    final public const RESOURCE_URL_COMMENT = 'comments';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [FileStreamedResponseFactory::class, FileManager::class, TranslatorInterface::class,
        ]);
    }

    #[Route(path: '/comments/{id}/files/{fileId}', name: 'download_comment_file', methods: 'GET', requirements: ['id' => '\d+'])]
    public function showCommentFile($id, $fileId)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::RESOURCE_URL_COMMENT, $id, $fileId));
    }

    #[Route(path: '/comments/{id}/files/{fileId}/delete', name: 'delete_comment_file', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function deleteCommentFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL_COMMENT, 'id' => 'id'])] ApiData $comment, $fileId)
    {
        if (!$this->isCsrfTokenValid('delete_comment_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('delete.errors', [], 'file_type'));

            return $this->redirect($request->headers->get('referer'));
        }

        $this->container->get(FileManager::class)->deleteFile($comment, self::RESOURCE_URL_COMMENT, \sprintf('files/%s', $fileId));
        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('delete.success', [], 'file_type'));

        return $this->redirect($request->headers->get('referer'));
    }
}

<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiPlatform\Metadata\Operation;
use App\Factory\FileResponseFactory;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Entity\LegacyFile;
use LegacyBundle\Factory\LegacyFileFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DownloadController extends AbstractController
{
    public function __construct(
        private readonly FileResponseFactory $fileResponseFactory,
        private readonly LegacyFileFactory $factory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request, int $id, string $method): BinaryFileResponse
    {
        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        $repository = $this->entityManager->getRepository($operation->getClass());

        $data = $repository->find($id);

        if (null === $data) {
            throw new NotFoundHttpException();
        }

        if (!method_exists($data, $method)) {
            throw new \InvalidArgumentException(\sprintf('The method %s does not exist for class %s', $method, $data::class));
        }

        if (null === ($fileId = $request->query->get('fileId'))) {
            throw new \InvalidArgumentException('toto');
        }

        $file = null;
        /** @var LegacyFile $legacyFile */
        foreach ($data->{$method}() as $legacyFile) {
            if ($legacyFile->id === (int) $fileId) {
                $file = $legacyFile;
                break;
            }
        }

        if (!$file instanceof LegacyFile) {
            throw $this->createNotFoundException('Attached file could not be found.');
        }

        try {
            $responseFile = $this->factory->createLegacyFile($file);
            $response = $this->fileResponseFactory->createFileResponse($responseFile);
            $response->setContentDisposition('inline', $responseFile->getFilename());
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('Attached file could not be found.');
        }

        return $response;
    }
}

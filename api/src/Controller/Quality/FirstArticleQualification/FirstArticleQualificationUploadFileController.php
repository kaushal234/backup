<?php

declare(strict_types=1);

namespace App\Controller\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualificationFile;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use App\Notifier\Quality\FirstArticleQualification\FirstArticleQualificationNotifier;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Workflow\Exception\LogicException;

class FirstArticleQualificationUploadFileController extends AbstractController
{
    private readonly WorkflowStatusUpdater $workflowStatusUpdater;
    private readonly NormalizerInterface $normalizer;
    private readonly EntityManagerInterface $entityManager;
    private readonly PersistableFileManagerFactory $persistableFileManagerRegistry;
    private readonly FirstArticleQualificationNotifier $notifier;

    public function __construct(
        WorkflowStatusUpdater $workflowStatusUpdater,
        PersistableFileManagerFactory $persistableFileManagerRegistry,
        NormalizerInterface $normalizer,
        EntityManagerInterface $entityManager,
        FirstArticleQualificationNotifier $notifier
    ) {
        $this->workflowStatusUpdater = $workflowStatusUpdater;
        $this->normalizer = $normalizer;
        $this->entityManager = $entityManager;
        $this->persistableFileManagerRegistry = $persistableFileManagerRegistry;
        $this->notifier = $notifier;
    }

    public function __invoke(FirstArticleQualification $firstArticleQualification, Request $request)
    {
        if (\in_array($firstArticleQualification->getStatus(), [FirstArticleQualification::REJECTED], true)) {
            throw new BadRequestHttpException('This action is disabled when FAQ is REJECTED');
        }

        $file = $request->files->get('file');
        $this->persistableFileManagerRegistry->getManagerForClass(FirstArticleQualificationFile::class)->attach(
            $firstArticleQualification,
            $file,
            ['maxNameLength' => 50, 'description' => $request->request->get('description')]
        );

        try {
            $this->workflowStatusUpdater->applyStatus($firstArticleQualification, FirstArticleQualification::IN_PROGRESS);
        } catch (LogicException $e) {
            // do nothing
        }

        $this->entityManager->persist($firstArticleQualification);
        $this->entityManager->flush();

        /** @var FirstArticleQualificationFile $addedFile */
        $addedFile = $firstArticleQualification->getFiles()->last();
        $file = $this->normalizer->normalize($addedFile, 'jsonld', ['groups' => ['file', 'people_public', 'expose_legacy'], 'jsonld_has_context' => true]);

        $this->notifier->sendFileAdded($firstArticleQualification, $addedFile->getPoster());

        return new JsonResponse($file, Response::HTTP_CREATED);
    }
}

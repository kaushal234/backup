<?php

declare(strict_types=1);

namespace App\Controller\Directory;

use App\Entity\Directory\People;
use App\Entity\Directory\PeopleFile;
use App\Factory\FileResponseFactory;
use App\Manager\Directory\PeopleManager;
use App\Manager\EntityFileManager;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

class PeoplePictureController extends AbstractController
{
    private readonly FileResponseFactory $fileResponseFactory;
    private readonly PeopleRepository $peopleRepository;
    private readonly PeopleManager $peopleManager;
    private readonly EntityFileManager $entityFileManager;
    private readonly ParameterBagInterface $parameters;

    public function __construct(
        FileResponseFactory $fileResponseFactory,
        PeopleRepository $peopleRepository,
        PeopleManager $peopleManager,
        ParameterBagInterface $parameters,
        EntityFileManager $entityFileManager
    ) {
        $this->fileResponseFactory = $fileResponseFactory;
        $this->peopleRepository = $peopleRepository;
        $this->peopleManager = $peopleManager;
        $this->entityFileManager = $entityFileManager;
        $this->parameters = $parameters;
    }

    public function __invoke($id, int $fileId, Request $request)
    {
        $search = $request->query->has('legacy') ? 'legacyId' : 'id';

        $people = $this->peopleRepository->findOneBy([$search => $id, 'disabled' => 0,  'hidden' => 0]);

        if (!$people instanceof People || !$this->peopleManager->hasPublicPicture($people) || null === $people->getPhoto()) {
            throw $this->createNotFoundException();
        }

        $file = $this->entityFileManager->getFileFromEntity(PeopleFile::class, $fileId, $people, 'people');

        try {
            $responseFile = new File($this->parameters->get('legacy.upload_dir').'/'.$file->getFilePath());
            $response = $this->fileResponseFactory->createFileResponse($responseFile);
            $response->setContentDisposition('inline', $responseFile->getFilename());
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('Attached file could not be found.');
        }

        return $response;
    }
}

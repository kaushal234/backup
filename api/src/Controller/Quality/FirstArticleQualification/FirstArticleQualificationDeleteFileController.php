<?php

declare(strict_types=1);

namespace App\Controller\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualificationFile;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FirstArticleQualificationDeleteFileController extends AbstractController
{
    private readonly PersistableFileManagerFactory $persistableFileManagerRegistry;
    private readonly ParameterBagInterface $parameters;

    public function __construct(
        PersistableFileManagerFactory $persistableFileManagerRegistry,
        ParameterBagInterface $parameters,
    ) {
        $this->persistableFileManagerRegistry = $persistableFileManagerRegistry;
        $this->parameters = $parameters;
    }

    public function __invoke(
        #[MapEntity(mapping: ['fileId' => 'id', 'id' => 'firstArticleQualification'])] FirstArticleQualificationFile $firstArticleQualificationFile
    ): Response {
        $faq = $firstArticleQualificationFile->getFirstArticleQualification();
        if (FirstArticleQualification::REJECTED === $faq->getStatus()) {
            throw new BadRequestHttpException('This action is disabled when FAQ is REJECTED');
        }

        try {
            $file = new File($this->parameters->get('legacy.upload_dir').'/'.$firstArticleQualificationFile->getFilePath());
        } catch (FileNotFoundException $e) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $this->persistableFileManagerRegistry->getManagerForClass(FirstArticleQualificationFile::class)->detach(
            $faq,
            $file
        );

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

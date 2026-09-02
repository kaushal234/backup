<?php

declare(strict_types=1);

namespace App\Controller\Support;

use App\Entity\Support\Manual;
use App\Entity\Support\ManualPrint;
use App\FileSystem\Zip\ZipManagerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ManualPrintController
{
    private readonly EntityManagerInterface $entityManager;
    private readonly ZipManagerFactory $zipManagerFactory;

    public function __construct(EntityManagerInterface $entityManager, ZipManagerFactory $zipManagerFactory)
    {
        $this->entityManager = $entityManager;
        $this->zipManagerFactory = $zipManagerFactory;
    }

    public function __invoke(ManualPrint $manualPrint): Response
    {
        if (null !== $manualPrint->downloadedAt) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, \sprintf('ManualPrint "%s" already downloaded.', $manualPrint->getId()));
        }

        $manualPrint->downloadedAt = new \DateTime();
        $this->entityManager->persist($manualPrint);
        $this->entityManager->flush();

        return $this->zipManagerFactory->getManagerForClass(Manual::class)->createZipResponse($manualPrint->manual);
    }
}

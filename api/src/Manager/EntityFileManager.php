<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityFileManager
{
    private readonly EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFileFromEntity(string $fileClass, int $fileId, $parentEntity, string $parentProperty): File
    {
        if (!class_exists($fileClass)) {
            throw new \InvalidArgumentException(\sprintf('The class %s does not exist', $fileClass));
        }

        $file = $this->em->getRepository($fileClass)->findOneBy(['id' => $fileId, $parentProperty => $parentEntity]);

        if (!$file instanceof File) {
            throw new NotFoundHttpException('Attached file could not be found.');
        }

        return $file;
    }
}

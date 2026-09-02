<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Activity\CommentFile;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class CommentFileController extends AbstractController
{
    private readonly PersistableFileManagerFactory $persistableFileManagerFactory;

    public function __construct(PersistableFileManagerFactory $persistableFileManagerFactory)
    {
        $this->persistableFileManagerFactory = $persistableFileManagerFactory;
    }

    public function __invoke($data, Request $request)
    {
        if (null !== ($file = $request->files->get('file'))) {
            $this->persistableFileManagerFactory->getManagerForClass(CommentFile::class)->attach($data, $file, [
                'description' => $data->fileDescription,
            ]);
        }

        return $data;
    }
}

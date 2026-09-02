<?php

declare(strict_types=1);

namespace App\Controller\File;

use App\FileSystem\Zip\ZipManagerFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ZipController extends AbstractController
{
    private readonly ZipManagerFactory $zipManagerFactory;

    public function __construct(ZipManagerFactory $zipManagerFactory)
    {
        $this->zipManagerFactory = $zipManagerFactory;
    }

    public function __invoke($data, Request $request): Response
    {
        $flatStructure = (bool) $request->query->get('flat');
        $formats = $request->query->all('formats');

        return $this->zipManagerFactory->getManagerForClass($data::class)->createZipResponse($data, $flatStructure, $formats);
    }
}

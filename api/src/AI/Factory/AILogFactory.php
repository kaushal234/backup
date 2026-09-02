<?php

declare(strict_types=1);

namespace App\AI\Factory;

use App\Entity\AI\AIFile;
use App\Entity\AI\AILog;
use App\Entity\AI\Request;
use App\Entity\AI\Response;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AILogFactory
{
    private ?UploadedFile $file = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PersistableFileManagerFactory $persistableFileManagerFactory,
    ) {
    }

    public function createRequest(string $url, array $options = [], ?UploadedFile $file = null, ?string $input = null): Request
    {
        $this->file = $file;

        $request = new Request();
        $request->url = $url;
        $request->options = $options;
        $request->content = $input;

        return $request;
    }

    public function createLog(Request $request, string $responseContent): AILog
    {
        $log = new AILog();

        $response = new Response();
        $response->content = $responseContent;

        $request->response = $response;

        $log->addRequest($request);

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        if (null !== $this->file) {
            $this->attachFile($request, $this->file);
        }

        return $log;
    }

    public function attachFile(Request $request, UploadedFile $file): void
    {
        $manager = $this->persistableFileManagerFactory->getManagerForClass(AIFile::class);
        $manager->attach($request, $file);
    }
}

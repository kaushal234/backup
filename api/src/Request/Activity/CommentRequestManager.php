<?php

declare(strict_types=1);

namespace App\Request\Activity;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\CommentFile;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use App\Request\SubRequestManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class CommentRequestManager
{
    public function __construct(
        private readonly SubRequestManager $subRequestManager,
        private readonly IriConverterInterface $iriConverter,
        private readonly PersistableFileManagerFactory $persistableFileManagerFactory
    ) {
    }

    public function insertComment($resource, string $message, ?UploadedFile $file = null, array $metadata = [], ?string $discriminator = null, bool $public = true)
    {
        $iri = $this->iriConverter->getIriFromResource($resource);
        $response = $this->subRequestManager->doSubRequest('api_comments_post_collection', [], Request::METHOD_POST, [
            'resource' => $iri,
            'message' => $message,
            'public' => $public,
            'metadata' => $metadata,
            'discriminator' => $discriminator,
        ]);

        if (null === $file) {
            return;
        }

        $comment = $this->iriConverter->getResourceFromIri(json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR)['@id']);
        $this->persistableFileManagerFactory->getManagerForClass(CommentFile::class)->attach($comment, $file);
    }
}

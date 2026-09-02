<?php

declare(strict_types=1);

namespace App\DataProcessor\DocumentTranslation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\DocumentTranslation;
use App\Http\DeeplClient;
use App\Repository\DocumentTranslationRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DocumentTranslationUploadProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ValidatorInterface $validator,
        private readonly DeeplClient $deeplClient,
        private readonly DocumentTranslationRepository $documentTranslationRepository,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
    ) {
    }

    /** @param DocumentTranslation $data */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $request = $this->requestStack->getCurrentRequest();

        $uploaded = $request->files->get('file');
        if (!$uploaded instanceof UploadedFile) {
            throw new BadRequestHttpException('Invalid or missing file.');
        }
        $document = $this->documentTranslationRepository->updateDocumentTranslationBeforeSendToDeepl(
            $data,
            $uploaded
        );

        $errors = $this->validator->validate($document);
        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }

        try {
            $deeplPayload = $this->deeplClient->uploadDocument($uploaded, $document->targetLang);
        } catch (\Throwable $e) {
            throw new BadRequestHttpException('DeepL upload failed: '.$e->getMessage());
        }

        $document = $this->documentTranslationRepository
            ->updateDocumentTranslationWithDeepLResponse($document, $deeplPayload);

        $errors = $this->validator->validate($document, null, ['post_upload']);
        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }

        return $this->persistProcessor->process($data, $operation);
    }
}

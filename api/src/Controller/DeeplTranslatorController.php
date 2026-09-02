<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Validator\ValidatorInterface;
use App\Dto\DeeplTranslator;
use App\Http\DeeplClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class DeeplTranslatorController extends AbstractController
{
    public function __construct(
        private readonly DeeplClient $deeplClient,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(DeeplTranslator $data)
    {
        $this->validator->validate($data);

        $translatedMessage = '';
        try {
            $translatedMessage = $this->deeplClient->getTranslation(
                $data->message,
                $data->targetLang,
                $data->module,
                $data->moduleId,
                true,
                $data->formality,
            );
        } catch (ExceptionInterface $exception) {
            // do nothing
        }

        return new JsonResponse(['translatedMessage' => $translatedMessage]);
    }
}

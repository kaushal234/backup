<?php

declare(strict_types=1);

namespace App\DataProcessor\Service;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Service\TechnicianOnCallEmailInput;
use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallNotifier;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class TechnicianOnCallEmailDataProcessor implements ProcessorInterface
{
    public function __construct(
        private TechnicianOnCallNotifier $notifier,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var TechnicianOnCallEmailInput $data */
        $technicianOnCall = $context['read_data'] ?? null;

        if (!$technicianOnCall instanceof TechnicianOnCall) {
            throw new NotFoundHttpException('TechnicianOnCall not found.');
        }

        $this->notifier->sendIntranetEmail(
            input: $data,
            technicianOnCall: $technicianOnCall,
            context: [
                'message' => $data->message,
            ],
        );

        return null;
    }
}

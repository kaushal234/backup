<?php

declare(strict_types=1);

namespace App\Manager\Service;

use App\Dto\Service\TechnicianOnCallDuplicateLineOutput;
use App\Request\SubRequestManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TechnicianOnCallDuplicateLineManager
{
    public function __construct(
        private SubRequestManager $subRequestManager,
    ) {
    }

    public function createFromPayload(array $payload): TechnicianOnCallDuplicateLineOutput
    {
        $outputLine = new TechnicianOnCallDuplicateLineOutput();

        try {
            $response = $this->subRequestManager->doSubRequest(
                'technician_on_call_create',
                [],
                Request::METHOD_POST,
                $payload
            );

            $content = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

            if ($response->getStatusCode() > 399) {
                $outputLine->technicianOnCallErrorMessage = $content['detail'] ?? 'Unknown error';

                return $outputLine;
            }

            $outputLine->serialNumber = $content['equipmentRecord']['serialNumber'] ?? '';
            $outputLine->technicianOnCallId = $content['id'] ?? null;

            if (Response::HTTP_CREATED === $response->getStatusCode() && isset($content['@sub_resources']['customerServiceRecord'])) {
                $outputLine->customerServiceRecordId = $content['@sub_resources']['customerServiceRecord']['id'] ?? null;
            }

            if (Response::HTTP_PARTIAL_CONTENT === $response->getStatusCode() && isset($content['@sub_resources']['customerServiceRecord'])) {
                $outputLine->customerServiceRecordErrorMessage = $content['@sub_resources']['customerServiceRecord']['detail']
                    ?? ($content['@sub_resources']['customerServiceRecord']['hydra:description'] ?? 'Unknown CSR error');
            }

            return $outputLine;
        } catch (\JsonException $e) {
            $outputLine->technicianOnCallErrorMessage = \sprintf('Invalid JSON response: %s', $e->getMessage());

            return $outputLine;
        } catch (\Throwable $e) {
            $outputLine->technicianOnCallErrorMessage = $e->getMessage();

            return $outputLine;
        }
    }
}
